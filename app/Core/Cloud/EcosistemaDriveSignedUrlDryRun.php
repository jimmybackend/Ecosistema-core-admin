<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveSignedUrlDryRun
{
    /**
     * @param array<string,mixed> $s3KeyValidation
     * @return array<string,mixed>
     */
    public function describe(array $s3KeyValidation): array
    {
        $validationStatus = (string)($s3KeyValidation['validation_status'] ?? 'fail');
        $eligibilityStatus = $validationStatus === 'pass' ? 'eligible' : ($validationStatus === 'warning' ? 'warning' : 'blocked');

        $safeReasons = [];
        foreach ((array)($s3KeyValidation['warnings'] ?? []) as $warning) {
            $safeReasons[] = (string)$warning;
        }
        foreach ((array)($s3KeyValidation['blocked_reasons'] ?? []) as $reason) {
            $safeReasons[] = (string)$reason;
        }

        if ($safeReasons === []) {
            $safeReasons[] = 'Metadata segura y validaciones dry-run sin bloqueos críticos.';
        }

        return [
            'mode' => 'dry-run',
            'signed_url_generated' => false,
            'aws_connection' => false,
            'download_enabled' => false,
            'expires_in_seconds_preview' => 900,
            'required_checks' => [
                'Sesión autenticada.',
                'Permiso cloud.view.',
                'Aislamiento tenant/user por política Drive.',
                'Archivo visible y activo en cloud_files.',
                'Validación segura de s3_key sin exponer la key.',
            ],
            'blocked_operations' => [
                'Generar signed URLs reales.',
                'Conectar AWS/S3 real.',
                'Descargar, stream o redirigir a S3.',
                'Aceptar s3_key/bucket/prefix/path/url por query o body.',
            ],
            'eligibility_status' => $eligibilityStatus,
            'safe_reasons' => $safeReasons,
            'next_step' => 'AWS/S3 config apagada; signed URL real queda para PR futuro',
        ];
    }
}
