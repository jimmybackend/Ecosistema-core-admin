<?php
declare(strict_types=1);
namespace App\Core\Platform;
final class EcosistemaPlatformCockpitService
{
    public function __construct(private EcosistemaPlatformCockpitRepository $repository, private EcosistemaPlatformAdapter $adapter){}
    public function buildCockpit(int $tenantId): array
    {
        if($tenantId<=0){return ['capabilities'=>$this->adapter->capabilities(),'modules'=>[],'tenant_summary'=>['roles_count'=>0,'users_count'=>0]];}
        $moduleCodes=['drive','url_locator','landing','browser_analytics','crm','mail_notifications','workflow']; $modules=[];
        foreach($this->repository->listModules($moduleCodes) as $row){$code=(string)($row['code']??'');$modules[]=['code'=>$code,'name'=>(string)($row['name']??$code),'status'=>(string)($row['status']??'unknown'),'description'=>(string)($row['description']??''),'feature_flags'=>$this->repository->listFeatureFlags($code,$tenantId,3)];}
        return ['capabilities'=>$this->adapter->capabilities(),'modules'=>$modules,'health_summary'=>$this->repository->summarizeHealthByModule($tenantId),'jobs_summary'=>$this->repository->summarizeJobsByModule($tenantId),'tenant_summary'=>['roles_count'=>$this->repository->countTenantRoles($tenantId),'users_count'=>$this->repository->countTenantUsers($tenantId)],'links'=>[['label'=>'Drive','href'=>'/cloud/drive'],['label'=>'URL Locator','href'=>'/url-locator'],['label'=>'Landing','href'=>'/landing'],['label'=>'Analytics','href'=>'/browser-analytics'],['label'=>'CRM','href'=>'/crm'],['label'=>'Mail/Notifications','href'=>'/mail-notifications'],['label'=>'Workflow','href'=>'/workflow']]];
    }
}
