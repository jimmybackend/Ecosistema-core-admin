<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmLeadService
{
    public function __construct(private EcosistemaCrmLeadRepository $repository, private EcosistemaCrmAdapter $adapter) {}

    public function listLeads(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeLeads($tenantId),
            'leads' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listLeads($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function toSafeDto(array $row): array
    {
        $contactName = trim((string) ($row['contact_name'] ?? ''));
        $email = trim((string) ($row['email'] ?? ''));
        $phone = trim((string) ($row['phone'] ?? ''));
        $notes = trim((string) ($row['notes'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'source_id' => isset($row['source_id']) ? (int) $row['source_id'] : null,
            'owner_user_id' => isset($row['owner_user_id']) ? (int) $row['owner_user_id'] : null,
            'company_name_preview' => $this->preview((string) ($row['company_name'] ?? ''), 60),
            'contact_name_present' => $contactName !== '',
            'contact_name_preview' => $this->maskText($contactName, 2),
            'email_present' => $email !== '',
            'email_preview' => $this->maskEmail($email),
            'phone_present' => $phone !== '',
            'phone_preview' => $this->maskPhone($phone),
            'interest_preview' => $this->preview((string) ($row['interest'] ?? ''), 80),
            'status' => (string) ($row['status'] ?? ''),
            'notes_present' => $notes !== '',
            'notes_preview' => $this->preview($notes, 80),
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trim = trim($value);
        if ($trim === '') { return null; }

        $head = mb_substr($trim, 0, $max);
        return $head === $trim ? $head : $head . '…';
    }

    private function maskText(string $value, int $visible): ?string
    {
        if ($value === '') { return null; }

        $prefix = mb_substr($value, 0, $visible);
        return $prefix . '***';
    }

    private function maskEmail(string $email): ?string
    {
        if ($email === '') { return null; }

        $atPos = strpos($email, '@');
        if ($atPos === false) {
            return $this->maskText($email, 2);
        }

        $local = substr($email, 0, $atPos);
        $domain = substr($email, $atPos + 1);
        $domainPreview = explode('.', $domain)[0] ?? '';

        return $this->maskText($local, 1) . '@' . $this->maskText($domainPreview, 1);
    }

    private function maskPhone(string $phone): ?string
    {
        if ($phone === '') { return null; }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '***';
        }

        $last = substr($digits, -2);
        return str_repeat('*', max(0, strlen($digits) - 2)) . $last;
    }
}
