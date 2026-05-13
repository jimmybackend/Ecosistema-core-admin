<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveAwsS3Config
{
    /**
     * @param array<string,mixed> $driveConfig
     */
    public function __construct(private array $driveConfig)
    {
    }

    /** @return array<string,mixed> */
    public function summary(): array
    {
        $enabled = (bool) ($this->driveConfig['aws_enabled'] ?? false);
        $mode = (string) ($this->driveConfig['mode'] ?? 'contract');
        $provider = (string) ($this->driveConfig['provider'] ?? 'aws-s3');
        $allowRemoteCalls = (bool) ($this->driveConfig['allow_remote_calls'] ?? false);
        $allowSignedUrls = (bool) ($this->driveConfig['allow_signed_urls'] ?? false);
        $allowRemoteUploads = (bool) ($this->driveConfig['allow_remote_uploads'] ?? false);
        $allowRemoteDownloads = (bool) ($this->driveConfig['allow_remote_downloads'] ?? false);

        $warnings = [];
        if (!$enabled) {
            $warnings[] = 'AWS/S3 real permanece apagado por configuración.';
        }

        return [
            'enabled' => $enabled,
            'mode' => $mode,
            'provider' => $provider,
            'region_configured' => trim((string) ($this->driveConfig['aws_region'] ?? '')) !== '',
            'bucket_configured' => trim((string) ($this->driveConfig['aws_bucket'] ?? '')) !== '',
            'credentials_configured' => trim((string) ($this->driveConfig['aws_access_key_id'] ?? '')) !== ''
                && trim((string) ($this->driveConfig['aws_secret_access_key'] ?? '')) !== '',
            'endpoint_configured' => trim((string) ($this->driveConfig['aws_endpoint'] ?? '')) !== '',
            'allow_remote_calls' => $allowRemoteCalls,
            'allow_signed_urls' => $allowSignedUrls,
            'allow_remote_uploads' => $allowRemoteUploads,
            'allow_remote_downloads' => $allowRemoteDownloads,
            'aws_connection' => false,
            'sdk_available' => false,
            'status' => 'prepared_but_disabled',
            'warnings' => $warnings,
            'blocked_operations' => [
                'Conexión real a AWS/S3.',
                'Generación de signed URLs reales.',
                'Descargas remotas desde S3.',
                'Subidas remotas a S3.',
            ],
        ];
    }
}
