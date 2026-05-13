<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveFileVersionService
{
    public function __construct(
        private EcosistemaDriveFileRepository $fileRepository,
        private EcosistemaDriveFileVersionRepository $versionRepository,
        private EcosistemaDriveAccessPolicy $policy,
        private EcosistemaDriveS3KeyValidator $validator,
    ) {
    }

    /**
     * @return array<int,array<string,mixed>>|null
     */
    public function listFileVersions(int $tenantId, int $userId, int $fileId, int $limit = 100): ?array
    {
        $file = $this->fileRepository->findVisibleById($tenantId, $userId, $fileId);
        if ($file === null || !$this->policy->canViewFileMetadata($file, $tenantId, $userId)) {
            return null;
        }

        $rows = $this->versionRepository->listForFile($tenantId, $fileId, $limit);
        $versions = [];

        foreach ($rows as $row) {
            $s3Key = isset($row['s3_key']) ? (string)$row['s3_key'] : '';
            $validation = $this->validator->validateShape($s3Key);
            $checksum = isset($row['checksum_sha256']) ? trim((string)$row['checksum_sha256']) : '';
            $warnings = isset($validation['warnings']) && is_array($validation['warnings']) ? $validation['warnings'] : [];

            if (isset($validation['blocked_reasons']) && is_array($validation['blocked_reasons']) && $validation['blocked_reasons'] !== []) {
                $warnings[] = 's3_key inválida por política interna.';
            }

            $versions[] = [
                'id' => isset($row['id']) ? (int)$row['id'] : 0,
                'file_id' => isset($row['file_id']) ? (int)$row['file_id'] : $fileId,
                'bucket_id' => isset($row['bucket_id']) ? (int)$row['bucket_id'] : null,
                'version_no' => isset($row['version_no']) ? (int)$row['version_no'] : 0,
                'size_bytes' => isset($row['size_bytes']) ? (int)$row['size_bytes'] : 0,
                'size_human' => $this->formatBytes(isset($row['size_bytes']) ? (int)$row['size_bytes'] : 0),
                'checksum_sha256_present' => $checksum !== '',
                'checksum_sha256_prefix' => $checksum !== '' ? substr($checksum, 0, 12) : null,
                'has_s3_key' => $s3Key !== '',
                's3_key_shape_status' => (string)($validation['key_shape_status'] ?? 'missing'),
                'has_s3_version_id' => isset($row['s3_version_id']) && trim((string)$row['s3_version_id']) !== '',
                's3_version_id_exposed' => false,
                'created_by_user_id' => isset($row['created_by_user_id']) ? (int)$row['created_by_user_id'] : null,
                'created_at' => (string)($row['created_at'] ?? ''),
                'mode' => 'read-only',
                'aws_connection' => false,
                'download_enabled' => false,
                'restore_enabled' => false,
                'upload_enabled' => false,
                'warnings' => $warnings,
            ];
        }

        return $versions;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = (float)$bytes;
        foreach ($units as $unit) {
            $value /= 1024;
            if ($value < 1024 || $unit === 'TB') {
                return number_format($value, 2) . ' ' . $unit;
            }
        }

        return $bytes . ' B';
    }
}
