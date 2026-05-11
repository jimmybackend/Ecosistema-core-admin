<?php
declare(strict_types=1);
namespace App\Core\Onboarding;
use PDO;
final readonly class OnboardingFlowRepository{public function __construct(private PDO $pdo){}
public function listAllWithSteps(): array{$stmt=$this->pdo->query('SELECT id, flow_key, name, description, target_type, is_active, created_at, updated_at FROM onboarding_flows ORDER BY id DESC');$flows=$stmt->fetchAll(PDO::FETCH_ASSOC)?:[];if($flows===[]){return [];} $ids=array_map(static fn(array $f): int=>(int)$f['id'],$flows);$in=implode(',',array_fill(0,count($ids),'?'));$stepStmt=$this->pdo->prepare("SELECT id, flow_id, step_key, name, description, sort_order, action_type, config_json, is_required, is_active, created_at FROM onboarding_steps WHERE flow_id IN ($in) ORDER BY flow_id ASC, sort_order ASC, id ASC");$stepStmt->execute($ids);$steps=$stepStmt->fetchAll(PDO::FETCH_ASSOC)?:[];$by=[];foreach($steps as $s){$by[(int)$s['flow_id']][]=$s;}foreach($flows as &$f){$f['steps']=$by[(int)$f['id']]??[];}return $flows;}
public function findActiveById(int $id): ?array{$stmt=$this->pdo->prepare('SELECT id, flow_key, name, description, target_type, is_active, created_at, updated_at FROM onboarding_flows WHERE id = :id AND is_active = 1 LIMIT 1');$stmt->execute([':id'=>$id]);$row=$stmt->fetch(PDO::FETCH_ASSOC);return is_array($row)?$row:null;}
public function listActive(): array{$stmt=$this->pdo->query('SELECT id, flow_key, name, description, target_type, is_active, created_at, updated_at FROM onboarding_flows WHERE is_active = 1 ORDER BY id DESC');return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[];}
public function listActiveStepsByFlow(int $flowId): array{$stmt=$this->pdo->prepare('SELECT id, flow_id, step_key, name, description, sort_order, action_type, config_json, is_required, is_active, created_at FROM onboarding_steps WHERE flow_id = :flow_id AND is_active = 1 ORDER BY sort_order ASC, id ASC');$stmt->execute([':flow_id'=>$flowId]);return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[];}
}
