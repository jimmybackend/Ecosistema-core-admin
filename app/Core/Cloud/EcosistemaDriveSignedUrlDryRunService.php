<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveSignedUrlDryRunService
{
    public function __construct(
        private EcosistemaDriveS3KeyValidationService $s3KeyValidationService,
        private EcosistemaDriveSignedUrlDryRun $contract,
    ) {
    }

    /** @return array<string,mixed>|null */
    public function evaluate(int $tenantId, int $userId, int $fileId): ?array
    {
        $validation = $this->s3KeyValidationService->validate($tenantId, $userId, $fileId);
        if ($validation === null) {
            return null;
        }

        return [
            'file_id' => (int)($validation['file_id'] ?? 0),
            'validation' => $validation,
            'signed_url_dry_run' => $this->contract->describe($validation),
        ];
    }
}
