<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use Aws\S3\S3Client;
use Throwable;

final class CloudS3Service
{
    public function __construct(private readonly array $config)
    {
    }

    public function isConfigured(): bool
    {
        $s3 = (array)($this->config['cloud']['s3'] ?? []);
        return trim((string)($s3['bucket'] ?? '')) !== '' && trim((string)($s3['region'] ?? '')) !== '';
    }

    public function checkBucket(): array
    {
        if (!class_exists(S3Client::class)) {
            return ['ok' => false, 'error_type' => 'cloud_config_error', 'message' => 'AWS SDK no disponible'];
        }
        try {
            $this->client()->headBucket(['Bucket' => $this->bucket()]);
            return ['ok' => true];
        } catch (Throwable $e) {
            return ['ok' => false, 'error_type' => 'cloud_s3_error', 'message' => $this->sanitize($e->getMessage())];
        }
    }

    public function putFile(string $key, string $localPath, string $mimeType): array
    {
        try {
            $this->client()->putObject([
                'Bucket' => $this->bucket(),
                'Key' => $key,
                'Body' => fopen($localPath, 'rb'),
                'ContentType' => $mimeType,
                'ServerSideEncryption' => 'AES256',
            ]);
            return ['ok' => true];
        } catch (Throwable $e) {
            return ['ok' => false, 'error_type' => 'cloud_upload_error', 'message' => $this->sanitize($e->getMessage())];
        }
    }

    public function getObject(string $key): array
    {
        try {
            $result = $this->client()->getObject(['Bucket' => $this->bucket(), 'Key' => $key]);
            return ['ok' => true, 'body' => (string)$result['Body']];
        } catch (Throwable $e) {
            return ['ok' => false, 'error_type' => 'cloud_download_error', 'message' => $this->sanitize($e->getMessage())];
        }
    }

    private function client(): S3Client
    {
        $s3 = (array)($this->config['cloud']['s3'] ?? []);
        $args = [
            'version' => 'latest',
            'region' => (string)($s3['region'] ?? 'us-east-1'),
            'credentials' => [
                'key' => (string)($s3['access_key_id'] ?? ''),
                'secret' => (string)($s3['secret_access_key'] ?? ''),
            ],
        ];
        if (trim((string)($s3['endpoint'] ?? '')) !== '') {
            $args['endpoint'] = (string)$s3['endpoint'];
            $args['use_path_style_endpoint'] = (bool)($s3['use_path_style_endpoint'] ?? false);
        }
        return new S3Client($args);
    }

    private function bucket(): string { return trim((string)($this->config['cloud']['s3']['bucket'] ?? '')); }
    private function sanitize(string $m): string { return preg_replace('/AKIA[0-9A-Z]{8,}/', '***', $m) ?? 'error'; }
}
