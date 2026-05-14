<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

use PDO;

final readonly class EcosistemaNotificationQueueRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listQueue(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));
        $sql = 'SELECT id,tenant_id,user_id,channel_id,template_id,module_code,entity_table,entity_id,title,body,payload_json,status,scheduled_at,sent_at,failed_at,fail_reason,created_at FROM notifications_queue WHERE tenant_id=:tenant_id ORDER BY created_at DESC,id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findQueueItem(int $tenantId, int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = 'SELECT id,tenant_id,user_id,channel_id,template_id,module_code,entity_table,entity_id,title,body,payload_json,status,scheduled_at,sent_at,failed_at,fail_reason,created_at FROM notifications_queue WHERE tenant_id=:tenant_id AND id=:id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function summarizeQueue(int $tenantId): array
    {
        $sql = 'SELECT COUNT(*) AS total, SUM(CASE WHEN status = :pending THEN 1 ELSE 0 END) AS pending_total, SUM(CASE WHEN status = :sent THEN 1 ELSE 0 END) AS sent_total, SUM(CASE WHEN status = :failed THEN 1 ELSE 0 END) AS failed_total FROM notifications_queue WHERE tenant_id=:tenant_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':pending', 'pending');
        $stmt->bindValue(':sent', 'sent');
        $stmt->bindValue(':failed', 'failed');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending_total' => (int) ($row['pending_total'] ?? 0),
            'sent_total' => (int) ($row['sent_total'] ?? 0),
            'failed_total' => (int) ($row['failed_total'] ?? 0),
        ];
    }
}
