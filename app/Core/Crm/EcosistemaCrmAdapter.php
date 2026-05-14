<?php

declare(strict_types=1);

namespace App\Core\Crm;

use App\Support\Env;

final class EcosistemaCrmAdapter
{
    public function capabilities(): array
    {
        $writeEnabled = filter_var(Env::get('ECOSISTEMA_CRM_ENABLED', 'false'), FILTER_VALIDATE_BOOL)
            && filter_var(Env::get('ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE', 'false'), FILTER_VALIDATE_BOOL);

        return [
            'campaigns_read' => true,
            'leads_read' => true,
            'lead_detail_read' => true,
            'lead_write' => $writeEnabled,
            'campaign_write' => false,
            'submission_to_lead_dry_run' => true,
            'submission_to_lead_write' => $writeEnabled,
            'mode' => $writeEnabled ? 'controlled-write' : 'read-only',
            'db_writes' => $writeEnabled,
        ];
    }
}
