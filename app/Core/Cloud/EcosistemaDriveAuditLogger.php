<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use App\Core\System\AuditLogger;
use PDO;

final readonly class EcosistemaDriveAuditLogger
{
    public function __construct(private PDO $pdo)
    {
    }

    public function logReadOnlyView(
        string $action,
        string $entityType,
        ?int $entityId,
        string $route,
        string $operation,
        int $tenantId,
        int $userId,
    ): void {
        (new AuditLogger($this->pdo))->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'new_values' => [
                'route' => $route,
                'mode' => 'read-only',
                'aws' => false,
                'operation' => $operation,
            ],
        ]);
    }
}
