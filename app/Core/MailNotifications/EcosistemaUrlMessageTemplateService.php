<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

final readonly class EcosistemaUrlMessageTemplateService
{
    public function __construct(private EcosistemaUrlMessageTemplateRepository $repository, private EcosistemaMailNotificationsAdapter $adapter)
    {
    }

    public function listTemplates(int $tenantId, int $limit = 100): array
    {
        return [
            'templates' => array_map(fn(array $row): array => $this->toTemplateDto($row), $this->repository->listTemplates($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getTemplate(int $tenantId, int $id): ?array
    {
        $row = $this->repository->findTemplate($tenantId, $id);
        if ($row === null) {
            return null;
        }

        $dto = $this->toTemplateDto($row);
        $attachments = array_map(fn(array $attachment): array => $this->toAttachmentDto($attachment), $this->repository->listAttachments($tenantId, $id));
        $accessLogs = $this->repository->listAccessLogs($tenantId, $id, 100);
        $dto['attachments'] = $attachments;
        $dto['attachments_summary'] = [
            'count' => count($attachments),
            'total_size_bytes' => array_sum(array_map(static fn(array $item): int => (int) ($item['size_bytes'] ?? 0), $attachments)),
        ];
        $dto['access_logs_summary'] = [
            'count' => count($accessLogs),
            'latest_at' => $accessLogs[0]['created_at'] ?? null,
            'ip_exposed' => false,
            'user_agent_exposed' => false,
            'referer_exposed' => false,
        ];

        return $dto;
    }

    private function toTemplateDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'short_link_id' => isset($row['short_link_id']) ? (int) $row['short_link_id'] : null,
            'campaign_id' => isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            'landing_page_id' => isset($row['landing_page_id']) ? (int) $row['landing_page_id'] : null,
            'template_name' => (string) ($row['template_name'] ?? ''),
            'subject' => (string) ($row['subject'] ?? ''),
            'from_name' => (string) ($row['from_name'] ?? ''),
            'from_email_preview' => $this->emailPreview((string) ($row['from_email'] ?? '')),
            'reply_to_email_preview' => $this->emailPreview((string) ($row['reply_to_email'] ?? '')),
            'header_html_present' => trim((string) ($row['header_html'] ?? '')) !== '',
            'header_html_exposed' => false,
            'body_html_present' => trim((string) ($row['body_html'] ?? '')) !== '',
            'body_html_preview' => $this->preview((string) ($row['body_html'] ?? ''), 120),
            'body_html_exposed' => false,
            'footer_html_present' => trim((string) ($row['footer_html'] ?? '')) !== '',
            'plain_text_present' => trim((string) ($row['plain_text'] ?? '')) !== '',
            'language_code' => (string) ($row['language_code'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'view_count' => isset($row['view_count']) ? (int) $row['view_count'] : 0,
            'unique_view_count' => isset($row['unique_view_count']) ? (int) $row['unique_view_count'] : 0,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'attachments_summary' => ['count' => 0, 'total_size_bytes' => 0],
            'access_logs_summary' => ['count' => 0, 'latest_at' => null, 'ip_exposed' => false],
            'mode' => 'read-only',
        ];
    }

    private function toAttachmentDto(array $row): array
    {
        return [
            'filename' => (string) ($row['filename'] ?? ''),
            'display_name' => (string) ($row['display_name'] ?? ''),
            'mime_type' => (string) ($row['mime_type'] ?? ''),
            'size_bytes' => isset($row['size_bytes']) ? (int) $row['size_bytes'] : 0,
            'file_path_present' => trim((string) ($row['file_path'] ?? '')) !== '',
            'file_path_exposed' => false,
            's3_key_present' => trim((string) ($row['s3_key'] ?? '')) !== '',
            's3_key_exposed' => false,
            'open_count' => isset($row['open_count']) ? (int) $row['open_count'] : 0,
            'download_count' => isset($row['download_count']) ? (int) $row['download_count'] : 0,
        ];
    }

    private function emailPreview(string $email): ?string
    {
        $email = trim($email);
        if ($email === '' || !str_contains($email, '@')) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);
        $localPreview = mb_substr($local, 0, 2);

        return $localPreview . str_repeat('*', max(0, mb_strlen($local) - mb_strlen($localPreview))) . '@' . $domain;
    }

    private function preview(string $value, int $max): ?string
    {
        $plain = trim(strip_tags($value));
        if ($plain === '') {
            return null;
        }

        $head = mb_substr($plain, 0, $max);

        return $head === $plain ? $head : $head . '…';
    }
}
