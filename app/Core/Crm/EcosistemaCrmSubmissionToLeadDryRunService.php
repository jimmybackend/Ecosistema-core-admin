<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmSubmissionToLeadDryRunService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function evaluate(int $tenantId, int $submissionId): ?array
    {
        if ($tenantId <= 0 || $submissionId <= 0) {
            return null;
        }

        $submission = $this->findSubmission($tenantId, $submissionId);
        if ($submission === null) {
            return null;
        }

        $valueMap = $this->findSubmissionValuesMap($tenantId, $submissionId);
        $mapped = $this->mappedFields($submission, $valueMap);

        $missingRequiredFields = [];
        foreach (['contact_name', 'email'] as $requiredField) {
            if (trim((string) ($mapped[$requiredField] ?? '')) === '') {
                $missingRequiredFields[] = $requiredField;
            }
        }

        $duplicateCandidatesCount = $this->countDuplicateCandidates($tenantId, (string) ($mapped['email'] ?? ''), (string) ($mapped['phone'] ?? ''));
        $warnings = [];
        if ($duplicateCandidatesCount > 0) {
            $warnings[] = 'Se detectaron posibles duplicados por email/teléfono.';
        }
        if ($missingRequiredFields !== []) {
            $warnings[] = 'Faltan campos requeridos para crear lead: ' . implode(', ', $missingRequiredFields) . '.';
        }

        return [
            'mode' => 'dry-run',
            'would_create_lead' => $missingRequiredFields === [],
            'would_link_campaign' => isset($submission['campaign_id']) && (int) $submission['campaign_id'] > 0,
            'would_update_submission' => false,
            'db_write' => false,
            'duplicate_candidates_count' => $duplicateCandidatesCount,
            'mapped_fields' => [
                'contact_name' => $this->preview((string) ($mapped['contact_name'] ?? ''), 40),
                'email' => $this->maskEmail((string) ($mapped['email'] ?? '')),
                'phone' => $this->maskPhone((string) ($mapped['phone'] ?? '')),
                'company_name' => $this->preview((string) ($mapped['company_name'] ?? ''), 40),
                'interest' => $this->preview((string) ($mapped['interest'] ?? ''), 60),
                'message' => $this->preview((string) ($mapped['message'] ?? ''), 80),
            ],
            'missing_required_fields' => $missingRequiredFields,
            'warnings' => $warnings,
            'pii_preview_only' => true,
        ];
    }

    private function findSubmission(int $tenantId, int $submissionId): ?array
    {
        $sql = 'SELECT id,tenant_id,campaign_id,contact_name,email,phone,company_name,interest,message FROM landing_form_submissions WHERE tenant_id=:tenant_id AND id=:submission_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function findSubmissionValuesMap(int $tenantId, int $submissionId): array
    {
        $stmt = $this->pdo->prepare('SELECT field_key,value_text FROM landing_form_submission_values WHERE tenant_id=:tenant_id AND submission_id=:submission_id ORDER BY id ASC');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $stmt->execute();
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $key = strtolower(trim((string) ($row['field_key'] ?? '')));
            if ($key !== '' && !array_key_exists($key, $map)) {
                $map[$key] = trim((string) ($row['value_text'] ?? ''));
            }
        }

        return $map;
    }

    private function mappedFields(array $submission, array $valueMap): array
    {
        $fields = ['contact_name', 'email', 'phone', 'company_name', 'interest', 'message'];
        $mapped = [];
        foreach ($fields as $field) {
            $submissionValue = trim((string) ($submission[$field] ?? ''));
            $mapped[$field] = $submissionValue !== '' ? $submissionValue : (string) ($valueMap[$field] ?? '');
        }

        return $mapped;
    }

    private function countDuplicateCandidates(int $tenantId, string $email, string $phone): int
    {
        $email = trim($email);
        $phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($email === '' && $phoneDigits === '') {
            return 0;
        }

        $clauses = [];
        $params = [':tenant_id' => $tenantId];

        if ($email !== '') {
            $clauses[] = 'LOWER(email)=LOWER(:email)';
            $params[':email'] = $email;
        }
        if ($phoneDigits !== '') {
            $clauses[] = 'REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", ""), ")", ""), "+", "") = :phone_digits';
            $params[':phone_digits'] = $phoneDigits;
        }

        $sql = 'SELECT COUNT(*) FROM crm_leads WHERE tenant_id=:tenant_id AND (' . implode(' OR ', $clauses) . ')';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $key === ':tenant_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function preview(string $value, int $max): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $head = mb_substr($value, 0, $max);

        return $head === $value ? $head : ($head . '…');
    }

    private function maskEmail(string $email): ?string
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }
        if (!str_contains($email, '@')) {
            return $this->preview($email, 4);
        }
        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1) . '***@' . $domain;
    }

    private function maskPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        return '***' . substr($digits, -4);
    }
}
