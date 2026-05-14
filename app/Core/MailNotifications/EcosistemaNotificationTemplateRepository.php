<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

use PDO;

final readonly class EcosistemaNotificationTemplateRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listTemplates(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));
        $sql = 'SELECT nt.id,nt.channel_id,nt.code,nt.name,nt.subject,nt.body,nt.variables_json,nt.is_active,nt.created_at,nt.updated_at,nc.code AS channel_code,nc.name AS channel_name FROM notifications_templates nt LEFT JOIN notifications_channels nc ON nc.id=nt.channel_id AND nc.tenant_id=nt.tenant_id WHERE nt.tenant_id=:tenant_id ORDER BY nt.updated_at DESC,nt.id DESC LIMIT :limit';
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

        $sql = 'SELECT nt.id,nt.channel_id,nt.code,nt.name,nt.subject,nt.body,nt.variables_json,nt.is_active,nt.created_at,nt.updated_at,nc.code AS channel_code,nc.name AS channel_name FROM notifications_templates nt LEFT JOIN notifications_channels nc ON nc.id=nt.channel_id AND nc.tenant_id=nt.tenant_id WHERE nt.tenant_id=:tenant_id AND nt.id=:id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
