<?php

declare(strict_types=1);

namespace App\Core\Mail;

use PDO;

final readonly class MailAttachmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listLogicalByMessageForUser(int $tenantId, int $userId, int $messageId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, original_name, mime_type, size_bytes, status, uploaded_at
             FROM cloud_files
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND origin_table = :origin_table
               AND origin_id = :origin_id
               AND status <> :deleted_status
             ORDER BY uploaded_at DESC, id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':origin_table', 'mail_messages');
        $stmt->bindValue(':origin_id', $messageId, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_status', 'deleted');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
