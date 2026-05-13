<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveS3DownloadService
{
    public function __construct(
        private PDO $pdo,
        private EcosistemaDriveS3KeyValidator $s3KeyValidator,
        private EcosistemaDriveAwsS3Config $awsConfig,
    ) {
    }

    /** @return array<string,mixed> */
    public function attempt(int $tenantId, int $userId, int $fileId): array
    {
        $result = [
            'allowed' => false,
            'blocked_reason' => 'blocked_by_default',
            'missing_flags' => [],
            'sdk_available' => class_exists('Aws\\S3\\S3Client'),
            'aws_connection' => false,
            'remote_downloads' => false,
            'signed_urls' => false,
            's3_key_validated' => false,
            's3_key_exposed' => false,
            'secrets_exposed' => false,
            'download_enabled' => false,
            'file_id' => $fileId,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
        ];

        if ($tenantId <= 0 || $userId <= 0 || $fileId <= 0) {
            $result['blocked_reason'] = 'invalid_context';
            return $result;
        }

        $file = $this->findFileForDownload($tenantId, $userId, $fileId);
        if ($file === null) {
            $result['blocked_reason'] = 'file_not_found';
            return $result;
        }

        $bucketId = (int)($file['bucket_id'] ?? 0);
        if ($bucketId <= 0) {
            $result['blocked_reason'] = 'invalid_bucket';
            return $result;
        }

        $status = (string)($file['status'] ?? '');
        if ($status !== 'active') {
            $result['blocked_reason'] = 'file_not_eligible';
            return $result;
        }

        $shape = $this->s3KeyValidator->validateShape(isset($file['s3_key']) ? (string)$file['s3_key'] : null);
        $result['s3_key_validated'] = $shape['key_shape_status'] === 'valid';
        if ($shape['key_shape_status'] !== 'valid') {
            $result['blocked_reason'] = 'invalid_s3_key';
            return $result;
        }

        $awsSummary = $this->awsConfig->summary();
        $result['aws_connection'] = false;
        $result['remote_downloads'] = (bool)($awsSummary['allow_remote_downloads'] ?? false);
        $result['signed_urls'] = (bool)($awsSummary['allow_signed_urls'] ?? false);

        $missingFlags = [];
        if (!($awsSummary['enabled'] ?? false)) $missingFlags[] = 'ECOSISTEMA_DRIVE_AWS_ENABLED=true';
        if (!($awsSummary['allow_remote_calls'] ?? false)) $missingFlags[] = 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=true';
        if (!($awsSummary['allow_remote_downloads'] ?? false)) $missingFlags[] = 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=true';
        if (($awsSummary['allow_signed_urls'] ?? false)) $missingFlags[] = 'ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false';
        if (($awsSummary['mode'] ?? '') !== 'controlled_download') $missingFlags[] = 'ECOSISTEMA_DRIVE_MODE=controlled_download';

        if ($missingFlags !== []) {
            $result['missing_flags'] = $missingFlags;
            $result['blocked_reason'] = 'missing_required_flags';
            return $result;
        }

        if (!($awsSummary['region_configured'] ?? false) || !($awsSummary['bucket_configured'] ?? false) || !($awsSummary['credentials_configured'] ?? false)) {
            $result['blocked_reason'] = 'aws_config_incomplete';
            return $result;
        }

        if (!$result['sdk_available']) {
            $result['blocked_reason'] = 'aws_sdk_not_available';
            return $result;
        }

        $result['blocked_reason'] = 'real_download_not_implemented';
        return $result;
    }

    /** @return array<string,mixed>|null */
    private function findFileForDownload(int $tenantId, int $userId, int $fileId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, tenant_id, user_id, bucket_id, status, deleted_at, s3_key, original_name, mime_type
             FROM cloud_files
             WHERE tenant_id = :tenant_id AND user_id = :user_id AND id = :id AND status <> :deleted_status
             LIMIT 1'
        );
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $fileId, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_status', 'deleted');
        $stmt->execute();

        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($file === false) {
            return null;
        }

        $deletedAt = trim((string)($file['deleted_at'] ?? ''));
        if ($deletedAt !== '') {
            return null;
        }

        return $file;
    }
}
