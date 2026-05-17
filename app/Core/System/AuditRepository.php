<?php
declare(strict_types=1);
namespace App\Core\System;
use PDO;
final readonly class AuditRepository{public function __construct(private PDO $pdo){} public function listRecent(int $limit=100): array {$stmt=$this->pdo->prepare('SELECT id,tenant_id,user_id,module_code,entity_table,entity_id,action,old_values,new_values,ip_address,user_agent,created_at FROM core_audit ORDER BY id DESC LIMIT :limit'); $stmt->bindValue(':limit',$limit,PDO::PARAM_INT); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[];}}
