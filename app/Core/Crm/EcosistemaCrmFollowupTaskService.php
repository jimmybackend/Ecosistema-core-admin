<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmFollowupTaskService
{
    public function __construct(private EcosistemaCrmFollowupTaskRepository $repository, private array $config = []) {}

    public function create(int $tenantId, int $leadId, int $createdByUserId, array $input): array
    {
        $enabled = (bool) ($this->config['followup_task_write'] ?? false);
        if (!$enabled) { return ['ok' => false, 'error' => 'Operación no habilitada por flags.']; }
        if ($tenantId <= 0 || $leadId <= 0 || $createdByUserId <= 0) { return ['ok' => false, 'error' => 'Contexto inválido.']; }

        $lead = $this->repository->findLead($tenantId, $leadId);
        if ($lead === null) { return ['ok' => false, 'error' => 'Lead no encontrado para el tenant actual.']; }

        $assignedUserId = (int) ($input['assigned_user_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $dueAt = trim((string) ($input['due_at'] ?? ''));
        $priority = strtolower(trim((string) ($input['priority'] ?? '')));

        if ($assignedUserId <= 0 || !$this->repository->userExists($tenantId, $assignedUserId)) { return ['ok' => false, 'error' => 'assigned_user_id inválido.']; }
        if ($title === '') { return ['ok' => false, 'error' => 'title es requerido.']; }
        if (!$this->isValidDatetime($dueAt)) { return ['ok' => false, 'error' => 'due_at inválido.']; }
        if (!in_array($priority, ['low', 'medium', 'high'], true)) { return ['ok' => false, 'error' => 'priority inválida.']; }

        $taskId = $this->repository->createTask($tenantId, [
            'assigned_user_id' => $assignedUserId,
            'created_by_user_id' => $createdByUserId,
            'lead_id' => $leadId,
            'title' => mb_substr($title, 0, 255),
            'description' => mb_substr($description, 0, 2000),
            'due_at' => $dueAt,
            'priority' => $priority,
        ]);

        return ['ok' => true, 'task_id' => $taskId, 'lead_id' => $leadId, 'status' => 'pending', 'pii_preview_only' => true];
    }

    private function isValidDatetime(string $value): bool
    {
        if ($value === '') { return false; }
        return \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value) !== false;
    }
}
