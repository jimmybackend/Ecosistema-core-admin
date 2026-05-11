<?php

declare(strict_types=1);

namespace App\Core\Dashboard;

use PDO;
use Throwable;

final class DashboardService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function build(array $auth): array
    {
        $tenantId = isset($auth['auth_tenant_id']) ? (int) $auth['auth_tenant_id'] : 0;
        $userId = isset($auth['auth_user_id']) ? (int) $auth['auth_user_id'] : 0;

        try {
            return [
                'hasError' => false,
                'tenant' => $this->findTenantById($tenantId),
                'activeUsersByTenant' => $this->countActiveUsersByTenant($tenantId),
                'activeModules' => $this->countActiveModules(),
                'activeSessionsByUser' => $this->countActiveSessionsByUser($userId),
                'modules' => $this->listActiveModules(),
            ];
        } catch (Throwable) {
            return [
                'hasError' => true,
                'tenant' => null,
                'activeUsersByTenant' => 0,
                'activeModules' => 0,
                'activeSessionsByUser' => 0,
                'modules' => [],
            ];
        }
    }

    private function findTenantById(int $tenantId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, slug, status, timezone, locale, created_at FROM core_tenants WHERE id = ? LIMIT 1');
        $statement->execute([$tenantId]);

        $tenant = $statement->fetch();
        return is_array($tenant) ? $tenant : null;
    }

    private function countActiveUsersByTenant(int $tenantId): int
    {
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM core_users WHERE tenant_id = ? AND status = 'active'");
        $statement->execute([$tenantId]);

        return (int) $statement->fetchColumn();
    }

    private function countActiveModules(): int
    {
        $statement = $this->pdo->query("SELECT COUNT(*) FROM core_modules WHERE status = 'active'");
        return (int) $statement->fetchColumn();
    }

    private function countActiveSessionsByUser(int $userId): int
    {
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM core_sessions WHERE user_id = ? AND revoked_at IS NULL AND expires_at > NOW()");
        $statement->execute([$userId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listActiveModules(): array
    {
        $statement = $this->pdo->query("SELECT code, name, is_core, is_billable, status FROM core_modules WHERE status = 'active' ORDER BY id ASC LIMIT 8");
        $modules = $statement->fetchAll();

        return is_array($modules) ? $modules : [];
    }
}
