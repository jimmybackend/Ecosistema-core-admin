<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveConfig
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function status(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'mode' => $this->mode(),
            'reference_repo' => $this->referenceRepo(),
            'remote_calls_blocked' => !$this->allowsRemoteCalls(),
            'signed_urls_blocked' => !$this->allowsSignedUrls(),
            'remote_uploads_blocked' => !$this->allowsRemoteUploads(),
            'remote_downloads_blocked' => !$this->allowsRemoteDownloads(),
            'api_timeout' => $this->apiTimeout(),
        ];
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    public function mode(): string
    {
        return (string) ($this->config['mode'] ?? 'contract');
    }

    public function referenceRepo(): string
    {
        return (string) ($this->config['reference_repo'] ?? 's3');
    }

    public function apiTimeout(): int
    {
        return max(1, (int) ($this->config['api_timeout'] ?? 5));
    }

    public function allowsRemoteCalls(): bool
    {
        return (bool) ($this->config['allow_remote_calls'] ?? false);
    }

    public function allowsSignedUrls(): bool
    {
        return (bool) ($this->config['allow_signed_urls'] ?? false);
    }

    public function allowsRemoteUploads(): bool
    {
        return (bool) ($this->config['allow_remote_uploads'] ?? false);
    }

    public function allowsRemoteDownloads(): bool
    {
        return (bool) ($this->config['allow_remote_downloads'] ?? false);
    }
}
