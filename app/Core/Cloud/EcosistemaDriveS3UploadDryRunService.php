<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveS3UploadDryRunService
{
    public function __construct(
        private EcosistemaDriveAwsS3Config $awsConfig,
        private EcosistemaDriveS3UploadDryRun $contract,
    ) {
    }

    /** @return array<string,mixed> */
    public function evaluate(): array
    {
        $awsSummary = $this->awsConfig->summary();

        $maxUploadMb = max(1, (int) env('CLOUD_MAX_UPLOAD_MB', 10));
        $extensionsRaw = (string) env('CLOUD_ALLOWED_EXTENSIONS', 'pdf,jpg,jpeg,png,txt,doc,docx,xls,xlsx');
        $allowedExtensions = array_values(array_filter(array_map(static fn (string $value): string => strtolower(trim($value)), explode(',', $extensionsRaw)), static fn (string $value): bool => $value !== ''));

        $allowRemoteUploads = filter_var((string) env('ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS', 'false'), FILTER_VALIDATE_BOOLEAN);
        $allowRemoteCalls = filter_var((string) env('ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS', 'false'), FILTER_VALIDATE_BOOLEAN);
        $awsEnabled = filter_var((string) env('ECOSISTEMA_DRIVE_AWS_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN);

        $safeReasons = ['No se aceptan archivos ni multipart en esta ruta.'];
        if (!$allowRemoteUploads) { $safeReasons[] = 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false.'; }
        if (!$allowRemoteCalls) { $safeReasons[] = 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false.'; }
        if (!$awsEnabled) { $safeReasons[] = 'ECOSISTEMA_DRIVE_AWS_ENABLED=false.'; }

        $eligibility = (!$allowRemoteUploads || !$allowRemoteCalls || !$awsEnabled) ? 'blocked' : 'warning';

        return $this->contract->describe([
            'max_upload_mb_preview' => $maxUploadMb,
            'allowed_extensions_preview' => $allowedExtensions,
            'required_checks' => [
                'Sesión autenticada.',
                'Permiso cloud.view.',
                'AWS/S3 habilitado explícitamente en configuración futura.',
                'allow_remote_calls=true y allow_remote_uploads=true en PR futuro.',
                'Validación de extensión/tamaño antes de cualquier operación remota futura.',
            ],
            'blocked_operations' => [
                'Subir archivo real a S3.',
                'Conectar AWS/S3 real para putObject.',
                'Leer $_FILES o mover archivos del request.',
                'Escribir metadata en DB (cloud_files/cloud_folders).',
                'Escribir en storage local.',
            ],
            'eligibility_status' => $eligibility,
            'safe_reasons' => array_merge($safeReasons, (array) ($awsSummary['warnings'] ?? [])),
        ]);
    }
}
