<?php

declare(strict_types=1);

namespace App\Core\Crm;

final class EcosistemaCrmAdapter
{
    public function capabilities(): array
    {
        return [
            'campaigns_read' => true,
            'leads_read' => false,
            'lead_detail_read' => false,
            'lead_write' => false,
            'campaign_write' => false,
            'submission_to_lead_dry_run' => false,
            'submission_to_lead_write' => false,
            'mode' => 'read-only',
            'db_writes' => false,
        ];
    }
}
