<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveS3UploadDryRun
{
    /**
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public function describe(array $input): array
    {
        $safeReasons = [];
        foreach ((array) ($input['safe_reasons'] ?? []) as $reason) {
            $safeReasons[] = (string) $reason;
        }

        if ($safeReasons === []) {
            $safeReasons[] = 'Subida real permanece bloqueada por seguridad en este PR.';
        }

        return [
            'mode' => 'dry-run',
            'upload_enabled' => false,
            'remote_upload_attempted' => false,
            'remote_upload_allowed' => false,
            'aws_connection' => false,
            'storage_write' => false,
            'db_write' => false,
            'max_upload_mb_preview' => max(1, (int) ($input['max_upload_mb_preview'] ?? 10)),
            'allowed_extensions_preview' => (array) ($input['allowed_extensions_preview'] ?? []),
            'required_checks' => (array) ($input['required_checks'] ?? []),
            'blocked_operations' => (array) ($input['blocked_operations'] ?? []),
            'eligibility_status' => (string) ($input['eligibility_status'] ?? 'blocked'),
            'safe_reasons' => $safeReasons,
            'next_step' => 'Subida real controlada queda para PR futuro',
        ];
    }
}
