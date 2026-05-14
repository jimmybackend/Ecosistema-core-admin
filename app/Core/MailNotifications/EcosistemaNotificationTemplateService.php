<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

final readonly class EcosistemaNotificationTemplateService
{
    public function __construct(private EcosistemaNotificationTemplateRepository $repository, private EcosistemaMailNotificationsAdapter $adapter)
    {
    }

    public function listTemplates(int $tenantId, int $limit = 100): array
    {
        return [
            'templates' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listTemplates($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getTemplate(int $tenantId, int $id): ?array
    {
        $row = $this->repository->findTemplate($tenantId, $id);

        return $row === null ? null : $this->toSafeDto($row);
    }

    private function toSafeDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'channel_id' => isset($row['channel_id']) ? (int) $row['channel_id'] : null,
            'channel_code' => (string) ($row['channel_code'] ?? ''),
            'channel_name' => (string) ($row['channel_name'] ?? ''),
            'code' => (string) ($row['code'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'subject' => (string) ($row['subject'] ?? ''),
            'body_present' => trim((string) ($row['body'] ?? '')) !== '',
            'body_preview' => $this->preview((string) ($row['body'] ?? ''), 160),
            'body_exposed' => false,
            'variables_json_present' => trim((string) ($row['variables_json'] ?? '')) !== '',
            'variables_json_exposed' => false,
            'is_active' => (bool) ($row['is_active'] ?? false),
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only',
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trimmed = trim($value);
        if ($trimmed == '') {
            return null;
        }

        $head = mb_substr($trimmed, 0, $max);

        return $head === $trimmed ? $head : $head . '…';
    }
}
