<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

final class EcosistemaUrlLocatorAdapter
{
    public function capabilities(): array
    {
        return [
            'links_read' => true,
            'link_detail_read' => true,
            'clicks_read' => true,
            'links_write' => false,
            'redirects_dry_run' => false,
            'public_redirects' => false,
            'click_tracking_write' => false,
            'mode' => 'read-only',
            'db_writes' => false,
        ];
    }
}
