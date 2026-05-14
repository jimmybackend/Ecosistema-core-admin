<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

final readonly class EcosistemaNotificationQueueService
{
    public function __construct(private EcosistemaNotificationQueueRepository $repository, private EcosistemaMailNotificationsAdapter $adapter)
    {
    }

    public function listQueue(int $tenantId, int $limit = 100): array
    {
        return [
            'items' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listQueue($tenantId, $limit)),
            'summary' => $this->repository->summarizeQueue($tenantId),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getQueueItem(int $tenantId, int $id): ?array
    {
        $row = $this->repository->findQueueItem($tenantId, $id);

        return $row === null ? null : $this->toSafeDto($row);
    }

    private function toSafeDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => isset($row['user_id']) ? (int) $row['user_id'] : null,
            'channel_id' => isset($row['channel_id']) ? (int) $row['channel_id'] : null,
            'template_id' => isset($row['template_id']) ? (int) $row['template_id'] : null,
            'module_code' => (string) ($row['module_code'] ?? ''),
            'entity_table' => (string) ($row['entity_table'] ?? ''),
            'entity_id' => isset($row['entity_id']) ? (int) $row['entity_id'] : null,
            'title_preview' => $this->preview((string) ($row['title'] ?? ''), 120),
            'body_present' => trim((string) ($row['body'] ?? '')) !== '',
            'body_preview' => $this->preview((string) ($row['body'] ?? ''), 160),
            'payload_json_present' => trim((string) ($row['payload_json'] ?? '')) !== '',
            'payload_json_exposed' => false,
            'status' => (string) ($row['status'] ?? ''),
            'scheduled_at' => $row['scheduled_at'] ?? null,
            'sent_at' => $row['sent_at'] ?? null,
            'failed_at' => $row['failed_at'] ?? null,
            'fail_reason_present' => trim((string) ($row['fail_reason'] ?? '')) !== '',
            'fail_reason_preview' => $this->preview((string) ($row['fail_reason'] ?? ''), 120),
            'created_at' => $row['created_at'] ?? null,
            'mode' => 'read-only',
            'processing_enabled' => false,
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $head = mb_substr($trimmed, 0, $max);

        return $head === $trimmed ? $head : $head . '…';
    }
}
