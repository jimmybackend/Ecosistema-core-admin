<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

use PDO;

final readonly class EcosistemaUrlMessageTemplateRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listTemplates(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));
        $sql = 'SELECT id,tenant_id,short_link_id,campaign_id,landing_page_id,template_name,subject,from_name,from_email,reply_to_email,header_html,body_html,footer_html,plain_text,language_code,status,view_count,unique_view_count,created_at,updated_at FROM url_message_templates WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findTemplate(int $tenantId, int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = 'SELECT id,tenant_id,short_link_id,campaign_id,landing_page_id,template_name,subject,from_name,from_email,reply_to_email,header_html,body_html,footer_html,plain_text,language_code,status,view_count,unique_view_count,created_at,updated_at FROM url_message_templates WHERE tenant_id=:tenant_id AND id=:id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listAttachments(int $tenantId, int $templateId): array
    {
        if ($templateId <= 0) {
            return [];
        }

        $sql = 'SELECT filename,display_name,file_path,s3_key,mime_type,size_bytes,open_count,download_count,sort_order,created_at FROM url_message_attachments WHERE tenant_id=:tenant_id AND message_template_id=:template_id ORDER BY sort_order ASC,id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':template_id', $templateId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listAccessLogs(int $tenantId, int $templateId, int $limit = 100): array
    {
        if ($templateId <= 0) {
            return [];
        }

        $safeLimit = max(1, min(200, $limit));
        $sql = 'SELECT id,template_id,short_link_id,event_type,ip_address,user_agent,referer,created_at FROM url_message_access_logs WHERE tenant_id=:tenant_id AND template_id=:template_id ORDER BY created_at DESC,id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':template_id', $templateId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
