<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveS3KeyValidationService
{
    public function __construct(
        private EcosistemaDriveS3KeyValidationRepository $repository,
        private EcosistemaDriveS3KeyValidator $validator,
    ) {
    }

    /** @return array<string,mixed>|null */
    public function validate(int $tenantId, int $userId, int $fileId): ?array
    {
        if ($tenantId <= 0 || $userId <= 0 || $fileId <= 0) {
            return null;
        }

        $file = $this->repository->findForValidation($tenantId, $userId, $fileId);
        if ($file === null) {
            return null;
        }

        $warnings = [];
        $blockedReasons = [];

        $bucketId = isset($file['bucket_id']) ? (int)$file['bucket_id'] : 0;
        if ($bucketId <= 0) {
            $blockedReasons[] = 'El archivo no tiene bucket_id válido.';
        }

        $status = (string)($file['status'] ?? '');
        if ($status !== 'active') {
            $warnings[] = 'El estado del archivo no es active; la validación futura podría bloquear descarga.';
        }

        $deletedAt = isset($file['deleted_at']) ? trim((string)$file['deleted_at']) : '';
        if ($deletedAt !== '') {
            $blockedReasons[] = 'El archivo aparece marcado como eliminado.';
        }

        $shape = $this->validator->validateShape(isset($file['s3_key']) ? (string)$file['s3_key'] : null);
        $warnings = array_values(array_merge($warnings, $shape['warnings']));
        $blockedReasons = array_values(array_merge($blockedReasons, $shape['blocked_reasons']));

        $validationStatus = $blockedReasons !== [] ? 'fail' : ($warnings !== [] ? 'warning' : 'pass');

        return [
            'file_id' => (int)($file['id'] ?? 0),
            'bucket_id' => $bucketId > 0 ? $bucketId : null,
            'status' => $status,
            'found_in_s3' => !empty($file['found_in_s3']),
            'has_s3_key' => $shape['key_shape_status'] !== 'missing',
            'key_shape_status' => $shape['key_shape_status'],
            'validation_status' => $validationStatus,
            'warnings' => $warnings,
            'blocked_reasons' => $blockedReasons,
            'mode' => 'dry-run/contract',
            'aws_connection' => false,
            'signed_url_generated' => false,
            'download_enabled' => false,
        ];
    }
}
