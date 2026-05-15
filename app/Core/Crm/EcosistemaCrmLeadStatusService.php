<?php

declare(strict_types=1);

namespace App\Core\Crm;

use App\Core\System\AuditLogger;

final readonly class EcosistemaCrmLeadStatusService
{
    public function __construct(private EcosistemaCrmLeadStatusRepository $repository, private AuditLogger $auditLogger, private array $config = []) {}

    public function getStatusContext(int $tenantId, int $leadId): array
    {
        if ($tenantId <= 0 || $leadId <= 0) { return ['ok' => false, 'error' => 'Contexto inválido.']; }
        $lead = $this->repository->findLead($tenantId, $leadId);
        if ($lead === null) { return ['ok' => false, 'error' => 'Lead no encontrado para tenant actual.']; }
        return ['ok' => true, 'lead_id' => $leadId, 'current_status' => (string) ($lead['status'] ?? ''), 'allowed_statuses' => $this->allowedStatuses(), 'write_enabled' => (bool)($this->config['lead_status_write'] ?? false)];
    }

    public function update(int $tenantId, int $leadId, int $userId, array $input): array
    {
        if (!(bool)($this->config['lead_status_write'] ?? false)) { return ['ok' => false, 'error' => 'Operación no habilitada por flags.']; }
        if ($tenantId <= 0 || $leadId <= 0 || $userId <= 0) { return ['ok' => false, 'error' => 'Contexto inválido.']; }
        $lead = $this->repository->findLead($tenantId, $leadId);
        if ($lead === null) { return ['ok' => false, 'error' => 'Lead no encontrado para tenant actual.']; }

        $newStatus = strtolower(trim((string)($input['status'] ?? '')));
        if (!in_array($newStatus, $this->allowedStatuses(), true)) { return ['ok' => false, 'error' => 'status inválido.']; }

        $currentStatus = strtolower((string)($lead['status'] ?? ''));
        if (!$this->isTransitionAllowed($currentStatus, $newStatus)) { return ['ok' => false, 'error' => 'Transición de estado no permitida.']; }

        $updatedLead = $this->repository->updateLeadStatus($tenantId, $leadId, $newStatus);
        $campaignUpdate = ['attempted' => false, 'updated' => false];

        $campaignLeadId = (int)($input['campaign_lead_id'] ?? 0);
        if ($campaignLeadId > 0) {
            $campaignUpdate['attempted'] = true;
            $campaignLead = $this->repository->findCampaignLead($tenantId, $leadId, $campaignLeadId);
            if ($campaignLead !== null) {
                $temperature = $this->normalizeTemperature((string)($input['temperature'] ?? ''));
                $score = $this->normalizeScore($input['score'] ?? null);
                $campaignUpdate['updated'] = $this->repository->updateCampaignLeadState($tenantId, $campaignLeadId, $newStatus, $temperature, $score);
            }
        }

        $this->auditLogger->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'entity_type' => 'crm_leads',
            'entity_id' => $leadId,
            'action' => 'lead_status_update',
            'old_values' => ['status' => $currentStatus],
            'new_values' => ['status' => $newStatus, 'campaign_update' => $campaignUpdate],
        ]);

        return ['ok' => $updatedLead, 'lead_id' => $leadId, 'previous_status' => $currentStatus, 'status' => $newStatus, 'campaign_update' => $campaignUpdate, 'pii_preview_only' => true];
    }

    private function allowedStatuses(): array { return ['new','contacted','qualified','proposal','won','lost']; }
    private function isTransitionAllowed(string $from, string $to): bool { if ($from === $to) { return true; } if ($from === 'won' || $from === 'lost') { return false; } return true; }
    private function normalizeTemperature(string $value): ?string { $value = strtolower(trim($value)); return in_array($value, ['cold','warm','hot'], true) ? $value : null; }
    private function normalizeScore(mixed $value): ?float { if ($value === null || $value === '') { return null; } if (!is_numeric($value)) { return null; } $score = (float)$value; return $score < 0 || $score > 100 ? null : $score; }
}
