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

    public function listAvailableCloudFilesForUser(int $tenantId, int $userId, int $limit = 200): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, original_name, mime_type, size_bytes, status, uploaded_at
             FROM cloud_files
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND status <> :deleted_status
             ORDER BY uploaded_at DESC, id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_status', 'deleted');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function replaceMessageAttachments(int $tenantId, int $userId, int $messageId, array $selectedFileIds): array
    {
        $selectedFileIds = array_values(array_unique(array_map(static fn (mixed $id): int => (int) $id, $selectedFileIds)));
        $selectedFileIds = array_values(array_filter($selectedFileIds, static fn (int $id): bool => $id > 0));

        $this->pdo->beginTransaction();
        try {
            $clear = $this->pdo->prepare(
                'UPDATE cloud_files
                 SET origin_table = NULL, origin_id = NULL, updated_at = NOW()
                 WHERE tenant_id = :tenant_id
                   AND user_id = :user_id
                   AND origin_table = :origin_table
                   AND origin_id = :origin_id'
            );
            $clear->execute([
                ':tenant_id' => $tenantId,
                ':user_id' => $userId,
                ':origin_table' => 'mail_messages',
                ':origin_id' => $messageId,
            ]);

            if ($selectedFileIds !== []) {
                $placeholders = implode(',', array_fill(0, count($selectedFileIds), '?'));
                $checkSql = 'SELECT id FROM cloud_files WHERE tenant_id = ? AND user_id = ? AND status <> ? AND id IN (' . $placeholders . ')';
                $check = $this->pdo->prepare($checkSql);
                $check->execute(array_merge([$tenantId, $userId, 'deleted'], $selectedFileIds));
                $validIds = array_map('intval', $check->fetchAll(PDO::FETCH_COLUMN) ?: []);
                sort($validIds);
                $expected = $selectedFileIds;
                sort($expected);
                if ($validIds !== $expected) {
                    $this->pdo->rollBack();
                    return ['ok' => false, 'reason' => 'Uno o más archivos no pertenecen al usuario/tenant autenticado.'];
                }

                $updateSql = 'UPDATE cloud_files SET origin_table = ?, origin_id = ?, updated_at = NOW() WHERE tenant_id = ? AND user_id = ? AND id IN (' . $placeholders . ')';
                $update = $this->pdo->prepare($updateSql);
                $params = array_merge(['mail_messages', $messageId, $tenantId, $userId], $selectedFileIds);
                $update->execute($params);
            }

            $this->pdo->commit();
            return ['ok' => true, 'reason' => 'Adjuntos lógicos actualizados correctamente.'];
        } catch (\Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'reason' => 'No se pudieron actualizar los adjuntos.'];
        }
    }

}
