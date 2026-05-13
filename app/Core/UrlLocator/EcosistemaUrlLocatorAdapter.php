<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

final class EcosistemaUrlLocatorAdapter
{
    public function __construct(private array $config = [])
    {
    }

    public function capabilities(): array
    {
        $enabled = (bool) ($this->config['enabled'] ?? false);
        $adminWrite = (bool) ($this->config['admin_write_enabled'] ?? false);
        $publicRedirects = $enabled && (bool) ($this->config['public_redirects_enabled'] ?? false);
        $tracking = $publicRedirects && (bool) ($this->config['tracking_enabled'] ?? false);

        return [
            'links_read' => true,
            'link_detail_read' => true,
            'clicks_read' => true,
            'links_write' => $enabled && $adminWrite,
            'redirects_dry_run' => true,
            'public_redirects' => $publicRedirects,
            'click_tracking_write' => $tracking,
            'redirect_real_enabled' => $publicRedirects,
            'mode' => $enabled && $adminWrite ? 'admin-controlled-write' : 'read-only',
            'db_writes' => $enabled && $adminWrite,
        ];
    }
}
