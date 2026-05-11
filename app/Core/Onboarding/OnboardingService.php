<?php
declare(strict_types=1);
namespace App\Core\Onboarding;
use PDO;
final readonly class OnboardingService{public function __construct(private PDO $pdo,private OnboardingFlowRepository $flows,private OnboardingRunRepository $runs){}
public function dashboard(int $tenantId): array{return ['stats'=>$this->runs->statsByTenant($tenantId),'runs'=>$this->runs->latestByTenant($tenantId,50)];}
public function listFlows(): array{return $this->flows->listAllWithSteps();}
public function createRun(int $tenantId,int $creatorId,array $input): string{$flow=$this->flows->findActiveById((int)($input['flow_id']??0));if($flow===null){return 'Flow no encontrado.';} $userIdRaw=trim((string)($input['user_id']??''));$userId=$userIdRaw===''?null:(int)$userIdRaw;if($userId!==null&&$this->runs->findUserForTenant($tenantId,$userId)===null){return 'Usuario no válido para este tenant.';} $contextRaw=trim((string)($input['context_json']??''));$contextJson=null;if($contextRaw!==''){json_decode($contextRaw,true);if(json_last_error()!==JSON_ERROR_NONE){return 'No se pudo guardar el onboarding run.';}$contextJson=$contextRaw;}
$steps=$this->flows->listActiveStepsByFlow((int)$flow['id']);$this->pdo->beginTransaction();try{$runId=$this->runs->createRun(['tenant_id'=>$tenantId,'user_id'=>$userId,'flow_id'=>(int)$flow['id'],'status'=>'pending','context_json'=>$contextJson,'created_by_user_id'=>$creatorId]);foreach($steps as $step){$this->runs->createRunStep($runId,(int)$step['id']);}$this->runs->createRunLog($runId,null,'info','Onboarding run creado.',json_encode(['flow_id'=>(int)$flow['id']],JSON_UNESCAPED_UNICODE));$this->pdo->commit();}catch(\Throwable){$this->pdo->rollBack();return 'No se pudo guardar el onboarding run.';} return 'Onboarding run creado correctamente.';}
}
