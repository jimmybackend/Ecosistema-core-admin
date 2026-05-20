<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class CloudFileRepository
{
    private const ALLOWED_ACCESS_TYPES = ['normal', 'secure', 'unlocked'];
    private const ALLOWED_STATUSES = ['active', 'archived', 'deleted', 'quarantined', 'missing'];
    private const ALLOWED_VIRUS_SCAN_STATUSES = ['pending', 'clean', 'infected', 'skipped', 'error'];

    public function __construct(private PDO $pdo)
    {
    }

    public function listByUser(int $tenantId, int $userId, int $limit = 100, ?string $status = null): array
    {
        $sql = 'SELECT id, original_name, extension, mime_type, size_bytes, status, virus_scan_status, access_type, found_in_s3, uploaded_at, s3_key FROM cloud_files WHERE tenant_id = :tenant_id AND user_id = :user_id';
        if ($status !== null && $status !== '') {
            $sql .= ' AND status = :status';
        } else {
            $sql .= ' AND status <> :status_deleted';
        }
        $sql .= ' ORDER BY uploaded_at DESC, id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($status !== null && $status !== '') { $stmt->bindValue(':status', $status); } else { $stmt->bindValue(':status_deleted', 'deleted'); }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByIdForUser(int $tenantId, int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, original_name, stored_name, s3_key, mime_type, extension, size_bytes, checksum_sha256, etag, storage_class, origin_module, origin_table, origin_id, access_type, secure_hint, encrypted, encryption_key_ref, found_in_s3, virus_scan_status, status, uploaded_at, updated_at FROM cloud_files WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function findDownloadableByIdForUser(int $tenantId, int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, original_name, stored_name, s3_key, mime_type, found_in_s3, status FROM cloud_files WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function archiveByIdForUser(int $tenantId, int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE cloud_files SET status = :status, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id');

        return $stmt->execute([':status' => 'archived', ':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]) && $stmt->rowCount() > 0;
    }

    public function trashByIdForUser(int $tenantId, int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE cloud_files SET status = :status, deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id');

        return $stmt->execute([':status' => 'deleted', ':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]) && $stmt->rowCount() > 0;
    }
    public function updateAfterMove(int $tenantId, int $userId, int $id, string $s3Key, string $status, ?string $deletedAt): bool
    {
        $stmt = $this->pdo->prepare('UPDATE cloud_files SET s3_key = :s3_key, status = :status, deleted_at = :deleted_at, found_in_s3 = 1, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id');
        return $stmt->execute([':s3_key' => $s3Key, ':status' => $status, ':deleted_at' => $deletedAt, ':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]) && $stmt->rowCount() > 0;
    }
    public function markPurged(int $tenantId, int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE cloud_files SET found_in_s3 = 0, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id AND status = :status');
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId, ':status' => 'deleted']) && $stmt->rowCount() > 0;
    }

    public function createUploaded(array $data): int
    {
        $validationError = $this->validateCloudFileEnums($data);
        if ($validationError !== null) {
            throw new \InvalidArgumentException(json_encode($validationError, JSON_UNESCAPED_UNICODE) ?: 'cloud_validation_error');
        }

        $bucketId = $this->resolveBucketId((int) $data['tenant_id'], (int) $data['user_id']);
        if ($bucketId <= 0) {
            return 0;
        }

        $rootId = $this->resolveRootId((int) $data['tenant_id'], (int) $data['user_id']);
        $folderId = $this->resolveFolderId((int) $data['tenant_id'], (int) $data['user_id'], $rootId);

        $stmt = $this->pdo->prepare('INSERT INTO cloud_files (tenant_id, user_id, bucket_id, root_id, folder_id, original_name, stored_name, s3_key, mime_type, extension, size_bytes, checksum_sha256, etag, origin_module, access_type, found_in_s3, virus_scan_status, status, uploaded_by_user_id, uploaded_at, updated_at) VALUES (:tenant_id, :user_id, :bucket_id, :root_id, :folder_id, :original_name, :stored_name, :s3_key, :mime_type, :extension, :size_bytes, :checksum_sha256, :etag, :origin_module, :access_type, :found_in_s3, :virus_scan_status, :status, :uploaded_by_user_id, NOW(), NOW())');
        $ok = $stmt->execute([
            ':tenant_id' => (int) $data['tenant_id'],
            ':user_id' => (int) $data['user_id'],
            ':bucket_id' => $bucketId,
            ':root_id' => $rootId,
            ':folder_id' => $folderId > 0 ? $folderId : null,
            ':original_name' => (string) $data['original_name'],
            ':stored_name' => (string) $data['stored_name'],
            ':s3_key' => (string) $data['s3_key'],
            ':mime_type' => (string) $data['mime_type'],
            ':extension' => (string) $data['extension'],
            ':size_bytes' => (int) $data['size_bytes'],
            ':checksum_sha256' => $data['checksum_sha256'],
            ':etag' => isset($data['etag']) ? (string) $data['etag'] : null,
            ':origin_module' => (string) $data['origin_module'],
            ':access_type' => (string) $data['access_type'],
            ':found_in_s3' => (int) $data['found_in_s3'],
            ':virus_scan_status' => (string) $data['virus_scan_status'],
            ':status' => (string) $data['status'],
            ':uploaded_by_user_id' => (int) $data['user_id'],
        ]);

        if (!$ok) {
            return 0;
        }

        $id = (int) $this->pdo->lastInsertId();
        if ($id > 0) {
            $this->createInitialVersion((int) $data['tenant_id'], (int) $data['user_id'], $id, $bucketId, (string) $data['s3_key'], (int) $data['size_bytes'], isset($data['checksum_sha256']) ? (string) $data['checksum_sha256'] : null);
            $this->createAccessLog((int) $data['tenant_id'], (int) $data['user_id'], $id, 'upload');
        }

        return $id;
    }

    public function validateCloudFileEnums(array $data): ?array
    {
        $enumMap = [
            'access_type' => self::ALLOWED_ACCESS_TYPES,
            'status' => self::ALLOWED_STATUSES,
            'virus_scan_status' => self::ALLOWED_VIRUS_SCAN_STATUSES,
        ];
        foreach ($enumMap as $field => $allowed) {
            $value = isset($data[$field]) ? (string) $data[$field] : '';
            if (!in_array($value, $allowed, true)) {
                return [
                    'reason' => 'cloud_validation_error',
                    'field' => $field,
                    'invalid_value' => $this->sanitizeValidationValue($value),
                ];
            }
        }

        return null;
    }

    private function sanitizeValidationValue(string $value): string
    {
        $trimmed = trim(preg_replace('/\s+/', ' ', $value) ?? '');
        if ($trimmed === '') {
            return '(empty)';
        }

        return mb_substr($trimmed, 0, 60);
    }

    private function resolveBucketId(int $tenantId, int $userId): int
    {
        $rootStmt = $this->pdo->prepare('SELECT bucket_id FROM cloud_user_roots WHERE tenant_id = :tenant_id AND user_id = :user_id AND status = :status ORDER BY id DESC LIMIT 1');
        $rootStmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':status' => 'active']);
        $rootBucketId = (int) ($rootStmt->fetchColumn() ?: 0);
        if ($rootBucketId > 0) {
            return $rootBucketId;
        }

        $bucketStmt = $this->pdo->prepare('SELECT id FROM cloud_buckets WHERE tenant_id = :tenant_id AND is_default = 1 ORDER BY id DESC LIMIT 1');
        $bucketStmt->execute([':tenant_id' => $tenantId]);

        return (int) ($bucketStmt->fetchColumn() ?: 0);
    }

    private function resolveRootId(int $tenantId, int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM cloud_user_roots WHERE tenant_id = :tenant_id AND user_id = :user_id AND status = :status ORDER BY id DESC LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':status' => 'active']);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function resolveFolderId(int $tenantId, int $userId, int $rootId): int
    {
        if ($rootId <= 0) {
            return 0;
        }
        $stmt = $this->pdo->prepare('SELECT id FROM cloud_folders WHERE tenant_id = :tenant_id AND user_id = :user_id AND root_id = :root_id AND name = :name AND is_deleted = 0 ORDER BY id DESC LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':root_id' => $rootId, ':name' => 'uploads']);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function createInitialVersion(int $tenantId, int $userId, int $fileId, int $bucketId, string $s3Key, int $sizeBytes, ?string $checksum): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO cloud_file_versions (tenant_id, file_id, bucket_id, version_no, s3_key, size_bytes, checksum_sha256, created_by_user_id, created_at) VALUES (:tenant_id, :file_id, :bucket_id, 1, :s3_key, :size_bytes, :checksum_sha256, :created_by_user_id, NOW())');
        $stmt->execute([':tenant_id' => $tenantId, ':file_id' => $fileId, ':bucket_id' => $bucketId, ':s3_key' => $s3Key, ':size_bytes' => $sizeBytes, ':checksum_sha256' => $checksum, ':created_by_user_id' => $userId]);
    }

    private function createAccessLog(int $tenantId, int $userId, int $fileId, string $action): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO cloud_file_access_logs (tenant_id, file_id, user_id, action, metadata_json, created_at) VALUES (:tenant_id, :file_id, :user_id, :action, :metadata_json, NOW())');
        $stmt->execute([':tenant_id' => $tenantId, ':file_id' => $fileId, ':user_id' => $userId, ':action' => $action, ':metadata_json' => json_encode(['source' => 'cloud'], JSON_UNESCAPED_UNICODE)]);
    }
}
