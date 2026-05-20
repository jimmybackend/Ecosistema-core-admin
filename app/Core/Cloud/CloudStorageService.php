<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use Aws\S3\S3Client;

final readonly class CloudStorageService
{
    public function __construct(private array $config, private bool $awsSdkAvailable)
    {
    }

    public function putObject(string $key, string $body, array $options = []): array
    {
        $normalized = $this->normalizeKey($key);
        if ($normalized === '') { return ['ok' => false, 'message' => 'S3 key inválida.']; }
        if (!$this->awsSdkAvailable) { return ['ok' => false, 'message' => 'AWS SDK no disponible.']; }
        $client = $this->makeClient();
        $result = $client->putObject(array_merge($this->baseParams($normalized), ['Body' => $body], $options));
        return ['ok' => true, 'key' => $normalized, 'etag' => (string) ($result['ETag'] ?? '')];
    }

    public function putFile(string $key, string $localPath, array $options = []): array
    {
        if (!is_file($localPath)) { return ['ok' => false, 'message' => 'Archivo local no encontrado.']; }
        return $this->putObject($key, (string) file_get_contents($localPath), $options);
    }

    public function objectExists(string $key): bool
    {
        if (!$this->awsSdkAvailable) { return false; }
        $normalized = $this->normalizeKey($key);
        if ($normalized === '') { return false; }
        return $this->makeClient()->doesObjectExistV2($this->bucket(), $normalized);
    }

    public function createPrefix(string $prefix): bool
    {
        $normalized = rtrim($this->normalizeKey($prefix), '/') . '/';
        if ($normalized === '/') { return false; }
        $result = $this->putObject($normalized . '.keep', '');
        return (bool) ($result['ok'] ?? false);
    }

    public function normalizeKey(string $key): string
    {
        $raw = str_replace('\\', '/', trim($key));
        $parts = array_values(array_filter(explode('/', $raw), static fn(string $part): bool => $part !== '' && $part !== '.' && $part !== '..'));
        return implode('/', $parts);
    }

    public function storeUploadedFile(array $file, string $internalName): array
    {
        if (!isset($file['tmp_name']) || !is_string($file['tmp_name'])) { return ['ok' => false, 'message' => 'Archivo inválido.']; }
        return $this->putFile($internalName, $file['tmp_name']);
    }

    private function makeClient(): S3Client
    {
        $s3 = (array) ($this->config['cloud']['s3'] ?? []);
        $args = [
            'version' => 'latest',
            'region' => (string) ($s3['region'] ?? 'us-east-1'),
            'credentials' => ['key' => (string) ($s3['access_key_id'] ?? ''), 'secret' => (string) ($s3['secret_access_key'] ?? '')],
            'use_path_style_endpoint' => (bool) ($s3['use_path_style_endpoint'] ?? false),
        ];
        if (trim((string) ($s3['endpoint'] ?? '')) !== '') { $args['endpoint'] = (string) $s3['endpoint']; }
        return new S3Client($args);
    }

    private function baseParams(string $key): array
    {
        return ['Bucket' => $this->bucket(), 'Key' => $key, 'ServerSideEncryption' => 'AES256'];
    }

    private function bucket(): string
    {
        return trim((string) (($this->config['cloud']['s3']['bucket'] ?? '')));
    }
}
