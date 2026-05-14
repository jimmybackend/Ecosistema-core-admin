<?php
declare(strict_types=1);
namespace App\Core\Platform;
use PDO;
final class EcosistemaPlatformCockpitRepository
{
    public function __construct(private PDO $pdo){}
    public function listModules(array $moduleCodes): array { if ($moduleCodes===[]) return []; $p=implode(',',array_fill(0,count($moduleCodes),'?')); $s=$this->pdo->prepare("SELECT id, code, name, description, status, is_core, is_billable, created_at FROM core_modules WHERE code IN ({$p}) ORDER BY name ASC"); foreach($moduleCodes as $i=>$c){$s->bindValue($i+1,$c,PDO::PARAM_STR);} $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[]; }
    public function listFeatureFlags(string $moduleCode,int $tenantId,int $limit=5): array { $s=$this->pdo->prepare('SELECT f.flag_key, f.name, f.default_enabled, f.status, t.is_enabled AS tenant_enabled, t.updated_at AS tenant_updated_at FROM core_feature_flags f LEFT JOIN core_tenant_feature_flags t ON t.feature_flag_id = f.id AND t.tenant_id = :tenant_id WHERE f.module_code = :module_code ORDER BY f.updated_at DESC, f.id DESC LIMIT :limit'); $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT);$s->bindValue(':module_code',$moduleCode,PDO::PARAM_STR);$s->bindValue(':limit',$limit,PDO::PARAM_INT);$s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[]; }
    public function summarizeHealthByModule(int $tenantId): array { $s=$this->pdo->prepare('SELECT d.module_code, COALESCE(r.status, "not_run") AS status, COUNT(*) AS total FROM system_health_check_definitions d LEFT JOIN system_health_check_runs r ON r.check_definition_id = d.id AND r.tenant_id = :tenant_id WHERE d.is_active = 1 GROUP BY d.module_code, COALESCE(r.status, "not_run") ORDER BY d.module_code ASC'); $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT); $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[]; }
    public function summarizeJobsByModule(int $tenantId): array { $s=$this->pdo->prepare('SELECT module_code, status, COUNT(*) AS total FROM system_jobs WHERE tenant_id = :tenant_id GROUP BY module_code, status ORDER BY module_code ASC, status ASC'); $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT); $s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[]; }
    public function countTenantRoles(int $tenantId): int { $s=$this->pdo->prepare('SELECT COUNT(*) FROM core_roles WHERE tenant_id = :tenant_id'); $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT); $s->execute(); return (int)$s->fetchColumn(); }
    public function countTenantUsers(int $tenantId): int { $s=$this->pdo->prepare('SELECT COUNT(*) FROM core_users WHERE tenant_id = :tenant_id'); $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT); $s->execute(); return (int)$s->fetchColumn(); }
}
