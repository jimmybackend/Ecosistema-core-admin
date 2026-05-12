<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class S3DriveIntegrationConfig
{
    public function __construct(private array $config)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    public function mode(): string
    {
        $mode = strtolower(trim((string) ($this->config['mode'] ?? 'contract')));
        return $mode !== '' ? $mode : 'contract';
    }

    public function isContractMode(): bool
    {
        return $this->mode() === 'contract';
    }

    public function areRemoteCallsAllowed(): bool
    {
        return (bool) ($this->config['allow_remote_calls'] ?? false);
    }

    public function areSignedUrlsAllowed(): bool
    {
        return (bool) ($this->config['allow_signed_urls'] ?? false);
    }

    public function areRemoteUploadsAllowed(): bool
    {
        return (bool) ($this->config['allow_remote_uploads'] ?? false);
    }

    public function areRemoteDownloadsAllowed(): bool
    {
        return (bool) ($this->config['allow_remote_downloads'] ?? false);
    }

    public function status(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'mode' => $this->mode(),
            'contract_mode' => $this->isContractMode(),
            'remote_calls_blocked' => !$this->areRemoteCallsAllowed(),
            'signed_urls_blocked' => !$this->areSignedUrlsAllowed(),
            'remote_uploads_blocked' => !$this->areRemoteUploadsAllowed(),
            'remote_downloads_blocked' => !$this->areRemoteDownloadsAllowed(),
            'aws_active' => false,
            'uses_external_s3_repo_calls' => false,
        ];
    }
}
