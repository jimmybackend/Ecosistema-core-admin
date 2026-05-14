<?php
declare(strict_types=1);
namespace App\Core\Platform;
final class EcosistemaPlatformAdapter
{
    public function capabilities(): array
    {
        return ['cockpit_read'=>true,'modules_read'=>true,'feature_flags_read'=>true,'health_read'=>true,'jobs_read'=>true,'roles_read'=>true,'db_writes'=>false,'mode'=>'read-only'];
    }
}
