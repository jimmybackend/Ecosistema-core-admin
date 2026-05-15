<?php

declare(strict_types=1);

namespace App\Core\Ai;

final readonly class EcosistemaAiAssistanceService
{
    public function __construct(private EcosistemaAiAssistanceRepository $repository, private EcosistemaAiProvider $provider, private array $config = []) {}

    public function assist(int $tenantId, int $userId, array $input): array
    {
        $enabled = (bool) ($this->config['enabled'] ?? false);
        $providerEnabled = (bool) ($this->config['provider_enabled'] ?? false);
        $writeEnabled = (bool) ($this->config['write_proposals'] ?? false);
        $leadId = isset($input['lead_id']) ? (int) $input['lead_id'] : 0;
        if ($tenantId <= 0 || $userId <= 0 || $leadId <= 0) {
            return ['ok' => false, 'allowed' => false, 'blocked_reason' => 'invalid_context'];
        }

        $lead = $this->repository->findLeadContext($tenantId, $leadId);
        if ($lead === null) {
            return ['ok' => false, 'allowed' => false, 'blocked_reason' => 'lead_not_found'];
        }

        $sanitized = $this->sanitizeLead($lead);
        if (!$enabled) {
            return ['ok' => true, 'allowed' => false, 'blocked_reason' => 'feature_disabled_by_flag', 'context' => $sanitized, 'provider_called' => false, 'proposal_id' => null];
        }

        $providerResult = $this->provider->assist(['summary_preview' => (string) ($sanitized['interest_preview'] ?? '')]);
        $proposalId = null;
        if ($enabled && $providerEnabled && $writeEnabled && $providerResult['called'] === true && is_array($providerResult['output'] ?? null)) {
            $proposalId = $this->repository->insertProposal($tenantId, $userId, (array) $providerResult['output']);
        }

        return [
            'ok' => true,
            'allowed' => $enabled,
            'blocked_reason' => null,
            'provider_called' => (bool) ($providerResult['called'] ?? false),
            'provider' => (string) ($providerResult['provider'] ?? 'none'),
            'proposal_persisted' => $proposalId !== null,
            'proposal_id' => $proposalId,
            'write_enabled' => $writeEnabled,
            'context' => $sanitized,
            'proposal_preview' => $providerResult['output'] ?? null,
            'pii_preview_only' => true,
        ];
    }

    private function sanitizeLead(array $lead): array
    {
        $email = trim((string) ($lead['email'] ?? ''));
        $phone = trim((string) ($lead['phone'] ?? ''));
        return [
            'lead_id' => (int) ($lead['id'] ?? 0),
            'company_name_preview' => $this->preview((string) ($lead['company_name'] ?? ''), 40),
            'contact_name_preview' => $this->maskText((string) ($lead['contact_name'] ?? ''), 2),
            'email_preview' => $this->maskEmail($email),
            'phone_preview' => $this->maskPhone($phone),
            'interest_preview' => $this->preview((string) ($lead['interest'] ?? ''), 90),
            'notes_present' => trim((string) ($lead['notes'] ?? '')) !== '',
            'status' => (string) ($lead['status'] ?? ''),
        ];
    }
    private function preview(string $value, int $max): ?string { $trim = trim($value); if ($trim === '') { return null; } $head = mb_substr($trim, 0, $max); return $head === $trim ? $head : $head . '…'; }
    private function maskText(string $value, int $visible): ?string { $value = trim($value); if ($value === '') { return null; } return mb_substr($value, 0, $visible) . '***'; }
    private function maskEmail(string $email): ?string { if ($email === '') { return null; } $atPos = strpos($email, '@'); if ($atPos === false) { return $this->maskText($email, 2); } $local = substr($email, 0, $atPos); $domain = substr($email, $atPos + 1); $domainPreview = explode('.', $domain)[0] ?? ''; return $this->maskText($local, 1) . '@' . $this->maskText($domainPreview, 1); }
    private function maskPhone(string $phone): ?string { if ($phone === '') { return null; } $digits = preg_replace('/\D+/', '', $phone) ?? ''; if ($digits === '') { return '***'; } return str_repeat('*', max(0, strlen($digits) - 2)) . substr($digits, -2); }
}
