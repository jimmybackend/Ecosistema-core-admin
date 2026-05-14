<?php
declare(strict_types=1);
namespace App\Core\Platform;
use PDO;
final readonly class EcosistemaPlatformHealthRepository
{
    public function __construct(private PDO $pdo){}
    public function listActiveModules(): array
    {
        $s=$this->pdo->prepare('SELECT id, code, name, description, status FROM core_modules WHERE status = :status ORDER BY name ASC');
        $s->execute([':status'=>'active']);
        return $s->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function listModuleChecksSummary(int $tenantId): array
    {
        $s=$this->pdo->prepare('SELECT d.module_code, COALESCE(MAX(r.finished_at), MAX(r.created_at)) AS last_run_at, SUM(CASE WHEN r.result_signal = "failed" OR r.status = "failed" THEN 1 ELSE 0 END) AS failed_count, SUM(CASE WHEN r.result_signal = "warning" OR r.status = "warning" THEN 1 ELSE 0 END) AS warning_count, SUM(CASE WHEN r.result_signal = "passed" OR r.status IN ("passed","success","completed") THEN 1 ELSE 0 END) AS passed_count FROM system_health_check_definitions d LEFT JOIN system_health_check_runs r ON r.check_definition_id = d.id AND r.tenant_id = :tenant_id WHERE d.is_active = 1 GROUP BY d.module_code');
        $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT);$s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function listModuleFindingsSummary(int $tenantId): array
    {
        $s=$this->pdo->prepare('SELECT module_code, SUM(CASE WHEN ternary_signal = "failed" THEN 1 ELSE 0 END) AS failed_findings, SUM(CASE WHEN ternary_signal = "warning" THEN 1 ELSE 0 END) AS warning_findings, SUM(CASE WHEN ternary_signal = "passed" THEN 1 ELSE 0 END) AS passed_findings FROM system_health_check_findings WHERE tenant_id = :tenant_id GROUP BY module_code');
        $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT);$s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function listModuleJobsSummary(int $tenantId): array
    {
        $s=$this->pdo->prepare('SELECT module_code, SUM(CASE WHEN status IN ("failed","error") THEN 1 ELSE 0 END) AS failed_jobs, SUM(CASE WHEN status IN ("pending","queued","running") THEN 1 ELSE 0 END) AS running_jobs, SUM(CASE WHEN status IN ("completed","success","done") THEN 1 ELSE 0 END) AS completed_jobs, MAX(updated_at) AS last_job_update_at FROM system_jobs WHERE tenant_id = :tenant_id GROUP BY module_code');
        $s->bindValue(':tenant_id',$tenantId,PDO::PARAM_INT);$s->execute(); return $s->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function listWorkersSummary(): array
    {
        $s=$this->pdo->query('SELECT status, COUNT(*) AS total, MAX(last_heartbeat_at) AS last_heartbeat_at FROM system_workers GROUP BY status');
        return $s->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function findModule(string $code): ?array
    {
        $s=$this->pdo->prepare('SELECT id, code, name, description, status FROM core_modules WHERE code = :code LIMIT 1');
        $s->execute([':code'=>$code]); $row=$s->fetch(PDO::FETCH_ASSOC); return is_array($row)?$row:null;
    }
}
