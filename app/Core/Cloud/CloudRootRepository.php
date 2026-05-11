<?php
declare(strict_types=1);
namespace App\Core\Cloud;
use PDO;
final readonly class CloudRootRepository{public function __construct(private PDO $pdo){}
public function listActiveByUser(int $tenantId,int $userId): array{$stmt=$this->pdo->prepare('SELECT id, bucket_id, root_prefix, display_name, quota_bytes, used_bytes, file_count, status FROM cloud_user_roots WHERE tenant_id = :tenant_id AND user_id = :user_id AND status = :status ORDER BY id DESC');$stmt->execute([':tenant_id'=>$tenantId,':user_id'=>$userId,':status'=>'active']);return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[];}
public function findActiveByIdForUser(int $tenantId,int $userId,int $id): ?array{$stmt=$this->pdo->prepare('SELECT id, tenant_id, user_id, bucket_id, root_prefix, display_name, quota_bytes, used_bytes, file_count, status FROM cloud_user_roots WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id AND status = :status LIMIT 1');$stmt->execute([':id'=>$id,':tenant_id'=>$tenantId,':user_id'=>$userId,':status'=>'active']);$row=$stmt->fetch(PDO::FETCH_ASSOC);return is_array($row)?$row:null;}}
