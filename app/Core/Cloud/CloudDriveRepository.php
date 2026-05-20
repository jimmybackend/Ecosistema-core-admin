<?php
declare(strict_types=1);
namespace App\Core\Cloud;
use PDO;

final readonly class CloudDriveRepository
{
    public function __construct(private PDO $pdo, private array $config){}
    public function findOrCreateDefaultBucket(int $tenantId): array { $p = new UserCloudRootProvisioner($this->pdo, new CloudStorageService($this->config, class_exists('Aws\\S3\\S3Client')), $this->config); $r = $p->provisionForUser($tenantId, 1); return ['id'=>(int)($r['bucket_id']??0)]; }
    public function findOrCreateUserRoot(int $tenantId, int $userId, int $bucketId): array { $p = new UserCloudRootProvisioner($this->pdo, new CloudStorageService($this->config, class_exists('Aws\\S3\\S3Client')), $this->config); $r = $p->provisionForUser($tenantId, $userId); return ['id'=>(int)($r['root_id']??0)]; }
    public function ensureSystemFolders(int $tenantId, int $userId, int $bucketId, int $rootId): array { $stmt=$this->pdo->prepare('SELECT id,name FROM cloud_folders WHERE tenant_id=? AND user_id=? AND root_id=? AND is_deleted=0'); $stmt->execute([$tenantId,$userId,$rootId]); return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[]; }
    public function listFolders(int $tenantId,int $userId,?int $parentFolderId): array { $sql='SELECT id,name,folder_type,parent_folder_id FROM cloud_folders WHERE tenant_id=? AND user_id=? AND is_deleted=0 AND '.($parentFolderId===null?'parent_folder_id IS NULL':'parent_folder_id=?').' ORDER BY name'; $stmt=$this->pdo->prepare($sql); $stmt->execute($parentFolderId===null?[$tenantId,$userId]:[$tenantId,$userId,$parentFolderId]); return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[]; }
    public function listFiles(int $tenantId,int $userId,?int $folderId): array { $sql='SELECT id,original_name,size_bytes,mime_type,status,uploaded_at FROM cloud_files WHERE tenant_id=? AND user_id=? AND '.($folderId===null?'folder_id IS NULL':'folder_id=?').' ORDER BY id DESC'; $stmt=$this->pdo->prepare($sql); $stmt->execute($folderId===null?[$tenantId,$userId]:[$tenantId,$userId,$folderId]); return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[]; }
}
