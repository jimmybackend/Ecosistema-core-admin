<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class CloudStorageConfig
{
    public function __construct(private array $config)
    {
    }

    public function toSafeArray(): array
    {
        $disk = (string) ($this->config['disk'] ?? 's3');
        $s3 = (array) ($this->config['s3'] ?? []);
        $accessKeyId = trim((string) ($s3['access_key_id'] ?? ''));

        return [
            'disk' => $disk,
            's3_enabled' => (bool) ($this->config['s3_enabled'] ?? false),
            'allow_downloads' => (bool) ($this->config['allow_downloads'] ?? false),
            'allow_uploads' => (bool) ($this->config['allow_uploads'] ?? false),
            'bucket' => trim((string) ($s3['bucket'] ?? '')),
            'region' => trim((string) ($s3['region'] ?? '')),
            'endpoint' => trim((string) ($s3['endpoint'] ?? '')),
            'use_path_style_endpoint' => (bool) ($s3['use_path_style_endpoint'] ?? false),
            'access_key_id_masked' => $this->maskAccessKeyId($accessKeyId),
            'is_valid' => $this->isLocallyValid(),
            'missing_fields' => $this->missingFields(),
        ];
    }

    public function isLocallyValid(): bool
    {
        return $this->missingFields() === [];
    }

    public function missingFields(): array
    {
        $missing = [];
        $s3 = (array) ($this->config['s3'] ?? []);

        if (trim((string) ($this->config['disk'] ?? '')) === '') {
            $missing[] = 'CLOUD_DISK';
        }
        if (trim((string) ($s3['access_key_id'] ?? '')) === '') {
            $missing[] = 'AWS_ACCESS_KEY_ID';
        }
        if (trim((string) ($s3['region'] ?? '')) === '') {
            $missing[] = 'AWS_DEFAULT_REGION';
        }
        if (trim((string) ($s3['bucket'] ?? '')) === '') {
            $missing[] = 'AWS_BUCKET';
        }

        return $missing;
    }

    private function maskAccessKeyId(string $accessKeyId): string
    {
        if ($accessKeyId === '') {
            return '(vacío)';
        }

        $length = strlen($accessKeyId);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($accessKeyId, 0, 4) . str_repeat('*', max(1, $length - 6)) . substr($accessKeyId, -2);
    }
}
