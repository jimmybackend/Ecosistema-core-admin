<?php

declare(strict_types=1);

namespace App\Core\Landing;

final class EcosistemaLandingAdapter
{
    public function capabilities(): array
    {
        return [
            'pages_read' => true,
            'page_detail_read' => true,
            'visits_read' => true,
            'forms_read' => true,
            'submissions_read' => false,
            'pages_write' => false,
            'public_render' => false,
            'visit_tracking_write' => false,
            'form_submit_write' => false,
            'crm_lead_write' => false,
            'mode' => 'read-only',
            'db_writes' => false,
        ];
    }
}
