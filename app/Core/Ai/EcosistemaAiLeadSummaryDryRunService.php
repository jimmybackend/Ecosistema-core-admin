<?php

declare(strict_types=1);

namespace App\Core\Ai;

final readonly class EcosistemaAiLeadSummaryDryRunService
{
    public function __construct(private EcosistemaAiLeadSummaryRepository $repository, private array $config = []) {}

    public function build(int $tenantId, int $leadId): array
    {
        $enabled = (bool) ($this->config['enabled'] ?? false);
        $dryRunEnabled = (bool) ($this->config['lead_summary_dry_run'] ?? false);
        if ($tenantId <= 0 || $leadId <= 0) {
            return ['ok' => false, 'allowed' => false, 'blocked_reason' => 'invalid_context'];
        }
        $lead = $this->repository->findLeadContext($tenantId, $leadId);
        if ($lead === null) {
            return ['ok' => false, 'allowed' => false, 'blocked_reason' => 'lead_not_found'];
        }
        $campaigns = $this->repository->listCampaignTouches($tenantId, $leadId);
        return [
            'ok' => true,
            'allowed' => $enabled && $dryRunEnabled,
            'blocked_reason' => ($enabled && $dryRunEnabled) ? null : 'feature_disabled_by_flags',
            'mode' => 'dry-run',
            'provider' => 'none',
            'proposal_persisted' => false,
            'external_ai_called' => false,
            'context' => [
                'lead' => $this->sanitizeLead($lead),
                'campaign_touches' => array_map(fn(array $row): array => $this->sanitizeCampaignTouch($row), $campaigns),
                'stats' => ['campaign_touches_count' => count($campaigns)],
                'sanitized' => true,
                'db_write' => false,
            ],
        ];
    }

    private function sanitizeLead(array $lead): array
    {
        $email = trim((string) ($lead['email'] ?? ''));
        $phone = trim((string) ($lead['phone'] ?? ''));
        return ['id' => (int) ($lead['id'] ?? 0),'source_id' => isset($lead['source_id']) ? (int) $lead['source_id'] : null,'owner_user_id' => isset($lead['owner_user_id']) ? (int) $lead['owner_user_id'] : null,'company_name_preview' => $this->preview((string) ($lead['company_name'] ?? ''), 60),'contact_name_present' => trim((string) ($lead['contact_name'] ?? '')) !== '','contact_name_preview' => $this->maskText((string) ($lead['contact_name'] ?? ''), 2),'email_present' => $email !== '','email_preview' => $this->maskEmail($email),'phone_present' => $phone !== '','phone_preview' => $this->maskPhone($phone),'interest_preview' => $this->preview((string) ($lead['interest'] ?? ''), 80),'status' => (string) ($lead['status'] ?? ''),'notes_present' => trim((string) ($lead['notes'] ?? '')) !== '','notes_preview' => $this->preview((string) ($lead['notes'] ?? ''), 80)];
    }

    private function sanitizeCampaignTouch(array $row): array
    {
        return ['id' => (int) ($row['id'] ?? 0),'campaign_id' => isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,'campaign_name_preview' => $this->preview((string) ($row['campaign_name'] ?? ''), 80),'campaign_code_preview' => $this->preview((string) ($row['campaign_code'] ?? ''), 30),'status' => (string) ($row['status'] ?? ''),'temperature' => (string) ($row['temperature'] ?? ''),'score' => isset($row['score']) ? (float) $row['score'] : null,'first_touch_at' => $row['first_touch_at'] ?? null,'last_touch_at' => $row['last_touch_at'] ?? null];
    }

    private function preview(string $value, int $max): ?string { $trim = trim($value); if ($trim === '') { return null; } $head = mb_substr($trim, 0, $max); return $head === $trim ? $head : $head . '…'; }
    private function maskText(string $value, int $visible): ?string { $value = trim($value); if ($value === '') { return null; } return mb_substr($value, 0, $visible) . '***'; }
    private function maskEmail(string $email): ?string { if ($email === '') { return null; } $atPos = strpos($email, '@'); if ($atPos === false) { return $this->maskText($email, 2); } $local = substr($email, 0, $atPos); $domain = substr($email, $atPos + 1); $domainPreview = explode('.', $domain)[0] ?? ''; return $this->maskText($local, 1) . '@' . $this->maskText($domainPreview, 1); }
    private function maskPhone(string $phone): ?string { if ($phone === '') { return null; } $digits = preg_replace('/\D+/', '', $phone) ?? ''; if ($digits === '') { return '***'; } return str_repeat('*', max(0, strlen($digits) - 2)) . substr($digits, -2); }
}
