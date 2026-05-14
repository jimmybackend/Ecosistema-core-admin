<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmSubmissionToLeadService
{
    public function __construct(private EcosistemaCrmLeadWriteRepository $repository) {}

    public function convert(int $tenantId, int $userId, int $submissionId, bool $forceDuplicate = false): array
    {
        $submission = $this->repository->findSubmissionForWrite($tenantId, $submissionId);
        if ($submission === null) { return ['ok' => false, 'error' => 'Submission no encontrada para el tenant actual.']; }
        if (!empty($submission['crm_lead_id'])) { return ['ok' => false, 'error' => 'Submission ya enlazada a un lead.']; }

        $mapped = $this->mapFields($submission);
        if (!$this->isValidEmail($mapped['email'])) { return ['ok'=>false,'error'=>'Email inválido o ausente.']; }
        if (!$this->isValidPhone($mapped['phone'])) { return ['ok'=>false,'error'=>'Phone inválido o ausente.']; }

        $duplicates = $this->repository->countDuplicateLeads($tenantId, $mapped['email'], preg_replace('/\D+/', '', $mapped['phone']) ?: '');
        if ($duplicates > 0 && !$forceDuplicate) {
            return ['ok'=>false,'error'=>'Posible duplicado detectado. Confirma explícitamente para continuar.','duplicate_candidates_count'=>$duplicates];
        }

        $leadId = 0; $campaignLeadId = null;
        try {
            $this->repository->beginTransaction();
            $leadId = $this->repository->createLeadFromSubmission($tenantId, [
                'source_id' => null,
                'owner_user_id' => $userId > 0 ? $userId : null,
                'company_name' => $mapped['company_name'],
                'contact_name' => $mapped['contact_name'],
                'email' => $mapped['email'],
                'phone' => $mapped['phone'],
                'interest' => $mapped['interest'],
                'status' => 'new',
                'notes' => $this->safeNotes($mapped['message']),
            ]);

            $campaignId = (int) ($submission['campaign_id'] ?? 0);
            if ($campaignId > 0) {
                $campaignLeadId = $this->repository->linkLeadToCampaign($tenantId, $campaignId, $leadId, $userId);
            }

            $updated = $this->repository->markSubmissionProcessed($tenantId, $submissionId, $leadId);
            if (!$updated) { throw new \RuntimeException('No se pudo actualizar submission.'); }
            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollBack();
            return ['ok' => false, 'error' => 'No se pudo completar la conversión de forma segura.'];
        }

        return ['ok'=>true,'lead_id'=>$leadId,'campaign_lead_id'=>$campaignLeadId,'duplicate_candidates_count'=>$duplicates,'pii_preview_only'=>true];
    }

    private function mapFields(array $submission): array
    {
        return [
            'contact_name' => trim((string) ($submission['contact_name'] ?? '')),
            'email' => trim((string) ($submission['email'] ?? '')),
            'phone' => trim((string) ($submission['phone'] ?? '')),
            'company_name' => trim((string) ($submission['company_name'] ?? '')),
            'interest' => trim((string) ($submission['interest'] ?? '')),
            'message' => trim((string) ($submission['message'] ?? '')),
        ];
    }

    private function safeNotes(string $message): ?string
    {
        $message = trim($message);
        if ($message === '') { return null; }
        $head = mb_substr($message, 0, 220);
        return $head === $message ? $head : $head . '…';
    }

    private function isValidEmail(string $email): bool { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
    private function isValidPhone(string $phone): bool { $d = preg_replace('/\D+/', '', $phone) ?? ''; return strlen($d) >= 7; }
}
