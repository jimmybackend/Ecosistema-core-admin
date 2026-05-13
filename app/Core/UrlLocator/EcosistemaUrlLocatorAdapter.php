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

        return [
            'links_read' => true,
            'link_detail_read' => true,
            'clicks_read' => true,
            'links_write' => $enabled && $adminWrite,
            'redirects_dry_run' => false,
            'public_redirects' => false,
            'click_tracking_write' => false,
            'redirect_real_enabled' => false,
            'mode' => $enabled && $adminWrite ? 'admin-controlled-write' : 'read-only',
            'db_writes' => $enabled && $adminWrite,
        ];
    }
}
