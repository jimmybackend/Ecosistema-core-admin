<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveS3UploadService
{
    public function __construct(
        private PDO $pdo,
        private EcosistemaDriveAwsS3Config $awsConfig,
        private EcosistemaDriveS3KeyValidator $s3KeyValidator,
    ) {
    }

    /** @return array<string,mixed> */
    public function describeAvailability(): array
    {
        $awsSummary = $this->awsConfig->summary();
        $missingFlags = $this->requiredFlags($awsSummary);

        return [
            'upload_enabled' => $missingFlags === [] && $this->isAwsConfigured($awsSummary) && class_exists('Aws\\S3\\S3Client'),
            'sdk_available' => class_exists('Aws\\S3\\S3Client'),
            'missing_flags' => $missingFlags,
            'max_upload_mb' => max(1, (int) env('CLOUD_MAX_UPLOAD_MB', 10)),
            'allowed_extensions' => $this->allowedExtensions(),
            'mode' => (string) ($awsSummary['mode'] ?? 'contract'),
            'blocked_reason' => $missingFlags === [] ? null : 'missing_required_flags',
        ];
    }
    /** @param array<string,mixed> $sessionContext @param array<string,mixed> $files */
    public function upload(array $sessionContext, array $files): array
    {
        $result = $this->describeAvailability() + ['created_file_id' => null, 'success' => false];
        $tenantId = (int) ($sessionContext['tenant_id'] ?? 0);
        $userId = (int) ($sessionContext['user_id'] ?? 0);
        $permissions = array_map('strval', (array) ($sessionContext['permissions'] ?? []));

        if ($tenantId <= 0 || $userId <= 0) { return $result + ['blocked_reason' => 'invalid_context']; }
        if (!in_array('cloud.manage', $permissions, true)) { return $result + ['blocked_reason' => 'permission_denied']; }
        if (!empty($result['missing_flags'])) { return $result; }
        if (empty($result['sdk_available'])) { return $result + ['blocked_reason' => 'aws_sdk_not_available', 'upload_enabled' => false]; }

        $awsSummary = $this->awsConfig->summary();
        if (!$this->isAwsConfigured($awsSummary)) { return $result + ['blocked_reason' => 'aws_config_incomplete']; }

        $file = $files['upload_file'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $result + ['blocked_reason' => 'invalid_file'];
        }

        $originalName = trim((string) ($file['name'] ?? ''));
        $tmpName = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($originalName === '' || $tmpName === '' || !is_uploaded_file($tmpName)) { return $result + ['blocked_reason' => 'invalid_file']; }
        if ($size <= 0 || $size > ((int) $result['max_upload_mb'] * 1024 * 1024)) { return $result + ['blocked_reason' => 'file_too_large']; }

        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '' || !in_array($extension, (array) $result['allowed_extensions'], true)) { return $result + ['blocked_reason' => 'extension_not_allowed']; }

        $detectedMime = (string) (mime_content_type($tmpName) ?: 'application/octet-stream');
        $safeOriginalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($originalName)) ?: 'file.' . $extension;
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $s3Key = trim((string) env('CLOUD_UPLOAD_PREFIX', 'uploads'), '/') . '/' . $tenantId . '/' . $userId . '/' . $storedName;
        $shape = $this->s3KeyValidator->validateShape($s3Key);
        if (($shape['key_shape_status'] ?? 'invalid') !== 'valid') { return $result + ['blocked_reason' => 'invalid_generated_s3_key']; }

        $bucketId = $this->resolveBucketId($tenantId, $userId);
        if ($bucketId <= 0) { return $result + ['blocked_reason' => 'bucket_not_resolved']; }

        try {
            /** @var \Aws\S3\S3Client $client */
            $client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => (string) ($awsSummary['region_configured'] ? env('ECOSISTEMA_DRIVE_AWS_REGION', '') : ''),
                'credentials' => ['key' => (string) env('ECOSISTEMA_DRIVE_AWS_ACCESS_KEY_ID', ''), 'secret' => (string) env('ECOSISTEMA_DRIVE_AWS_SECRET_ACCESS_KEY', '')],
            ]);
            $bucket = trim((string) ($awsSummary['bucket'] ?? env('ECOSISTEMA_DRIVE_AWS_BUCKET', '')));
            if ($bucket === '') { return $result + ['blocked_reason' => 'aws_bucket_not_configured']; }

            $client->putObject([
                'Bucket' => $bucket,
                'Key' => $s3Key,
                'Body' => fopen($tmpName, 'rb'),
                'ContentType' => $detectedMime,
                'Metadata' => ['origin' => 'ecosistema-core-admin', 'tenant_id' => (string) $tenantId],
            ]);

            $stmt = $this->pdo->prepare('INSERT INTO cloud_files (tenant_id, user_id, bucket_id, original_name, stored_name, s3_key, mime_type, extension, size_bytes, status, access_type, encrypted, found_in_s3, origin_module, uploaded_by_user_id, uploaded_at, updated_at) VALUES (:tenant_id,:user_id,:bucket_id,:original_name,:stored_name,:s3_key,:mime_type,:extension,:size_bytes,:status,:access_type,:encrypted,:found_in_s3,:origin_module,:uploaded_by_user_id,NOW(),NOW())');
            $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':bucket_id', $bucketId, PDO::PARAM_INT);
            $stmt->bindValue(':original_name', $safeOriginalName);
            $stmt->bindValue(':stored_name', $storedName);
            $stmt->bindValue(':s3_key', $s3Key);
            $stmt->bindValue(':mime_type', $detectedMime);
            $stmt->bindValue(':extension', $extension);
            $stmt->bindValue(':size_bytes', $size, PDO::PARAM_INT);
            $stmt->bindValue(':status', 'active');
            $stmt->bindValue(':access_type', 'normal');
            $stmt->bindValue(':encrypted', 0, PDO::PARAM_INT);
            $stmt->bindValue(':found_in_s3', 1, PDO::PARAM_INT);
            $stmt->bindValue(':origin_module', 'drive_controlled_upload');
            $stmt->bindValue(':uploaded_by_user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result['created_file_id'] = (int) $this->pdo->lastInsertId();
            $result['success'] = true;
            $result['upload_enabled'] = true;
            $result['blocked_reason'] = null;
            return $result;
        } catch (\Throwable) {
            return $result + ['blocked_reason' => 'upload_failed'];
        }
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
    /** @return array<int,string> */
    private function allowedExtensions(): array
    {
        return array_values(array_filter(array_map(static fn (string $v): string => strtolower(trim($v)), explode(',', (string) env('CLOUD_ALLOWED_EXTENSIONS', 'pdf,jpg,jpeg,png,txt,doc,docx,xls,xlsx')))));
    }
    /** @param array<string,mixed> $awsSummary @return array<int,string> */
    private function requiredFlags(array $awsSummary): array
    {
        $missing = [];
        if (!filter_var((string) env('ECOSISTEMA_DRIVE_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN)) $missing[] = 'ECOSISTEMA_DRIVE_ENABLED=true';
        if (!($awsSummary['enabled'] ?? false)) $missing[] = 'ECOSISTEMA_DRIVE_AWS_ENABLED=true';
        if (!($awsSummary['allow_remote_calls'] ?? false)) $missing[] = 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=true';
        if (!($awsSummary['allow_remote_uploads'] ?? false)) $missing[] = 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=true';
        if ((string) ($awsSummary['mode'] ?? '') !== 'controlled_upload') $missing[] = 'ECOSISTEMA_DRIVE_MODE=controlled_upload';
        if (!filter_var((string) env('CLOUD_ALLOW_UPLOADS', 'false'), FILTER_VALIDATE_BOOLEAN)) $missing[] = 'CLOUD_ALLOW_UPLOADS=true';
        if (!filter_var((string) env('CLOUD_S3_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN)) $missing[] = 'CLOUD_S3_ENABLED=true';
        return $missing;
    }
    /** @param array<string,mixed> $awsSummary */
    private function isAwsConfigured(array $awsSummary): bool
    {
        return !empty($awsSummary['region_configured']) && !empty($awsSummary['bucket_configured']) && !empty($awsSummary['credentials_configured']);
    }
}

