<?php
declare(strict_types=1);
namespace App\Core\Platform;
final readonly class EcosistemaPlatformHealthService
{
    public function __construct(private EcosistemaPlatformHealthRepository $repository){}
    public function buildDashboard(int $tenantId): array
    {
        if($tenantId<=0){return ['modules'=>[],'workers'=>[]];}
        $checks=[]; foreach($this->repository->listModuleChecksSummary($tenantId) as $row){$checks[(string)$row['module_code']]=$row;}
        $findings=[]; foreach($this->repository->listModuleFindingsSummary($tenantId) as $row){$findings[(string)$row['module_code']]=$row;}
        $jobs=[]; foreach($this->repository->listModuleJobsSummary($tenantId) as $row){$jobs[(string)$row['module_code']]=$row;}
        $modules=[];
        foreach($this->repository->listActiveModules() as $m){$code=(string)($m['code']??'');$c=$checks[$code]??[];$f=$findings[$code]??[];$j=$jobs[$code]??[];$failed=(int)($c['failed_count']??0)+(int)($f['failed_findings']??0)+(int)($j['failed_jobs']??0);$warning=(int)($c['warning_count']??0)+(int)($f['warning_findings']??0);$status=$failed>0?'failed':($warning>0?'warning':'passed');$modules[]=['code'=>$code,'name'=>(string)($m['name']??$code),'description'=>(string)($m['description']??''),'status'=>$status,'last_run_at'=>(string)($c['last_run_at']??''),'failed'=>$failed,'warning'=>$warning,'passed'=>(int)($c['passed_count']??0)+(int)($f['passed_findings']??0),'running_jobs'=>(int)($j['running_jobs']??0),'completed_jobs'=>(int)($j['completed_jobs']??0)];}
        return ['modules'=>$modules,'workers'=>$this->repository->listWorkersSummary()];
    }
    public function buildModuleDetail(int $tenantId,string $code): ?array
    {
        if($tenantId<=0||$code===''){return null;} $module=$this->repository->findModule($code); if(!is_array($module)){return null;}
        foreach($this->buildDashboard($tenantId)['modules'] as $item){if((string)$item['code']===$code){return ['module'=>$module,'health'=>$item,'workers'=>$this->repository->listWorkersSummary()];}}
        return ['module'=>$module,'health'=>['code'=>$code,'status'=>'passed','failed'=>0,'warning'=>0,'passed'=>0,'running_jobs'=>0,'completed_jobs'=>0,'last_run_at'=>''],'workers'=>$this->repository->listWorkersSummary()];
    }
}
