<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

use PDO;

final readonly class EcosistemaUrlLocatorLinkWriteRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function slugExists(int $tenantId, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM url_short_links WHERE tenant_id=:tenant_id AND slug=:slug';
        if ($exceptId !== null && $exceptId > 0) {
            $sql .= ' AND id<>:except_id';
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':slug', $slug);
        if ($exceptId !== null && $exceptId > 0) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function campaignBelongsToTenant(int $tenantId, int $campaignId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function landingPageBelongsToTenant(int $tenantId, int $landingPageId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM landing_pages WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $landingPageId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function languageIsActive(string $code): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM url_languages WHERE code=:code AND is_active=1 LIMIT 1');
        $stmt->bindValue(':code', $code);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function createLink(int $tenantId, int $userId, array $data): int
    {
        $fields = ['tenant_id','created_by_user_id','slug','target_url','original_url_after_ads','default_language_code','language_detection_enabled','language_fallback_url','language_query_param','title','description','status','smart_type','requires_access_token','expires_at','max_clicks','utm_source','utm_medium','utm_campaign','utm_term','utm_content','campaign_id','landing_page_id'];
        $sql = 'INSERT INTO url_short_links (' . implode(',', $fields) . ',updated_at) VALUES (:tenant_id,:created_by_user_id,:slug,:target_url,:original_url_after_ads,:default_language_code,:language_detection_enabled,:language_fallback_url,:language_query_param,:title,:description,:status,:smart_type,:requires_access_token,:expires_at,:max_clicks,:utm_source,:utm_medium,:utm_campaign,:utm_term,:utm_content,:campaign_id,:landing_page_id,NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($data, ['tenant_id' => $tenantId, 'created_by_user_id' => $userId, 'requires_access_token' => 0]));

        return (int) $this->pdo->lastInsertId();
    }

    public function updateLink(int $tenantId, int $linkId, array $data): bool
    {
        $sql = 'UPDATE url_short_links SET slug=:slug,target_url=:target_url,original_url_after_ads=:original_url_after_ads,default_language_code=:default_language_code,language_detection_enabled=:language_detection_enabled,language_fallback_url=:language_fallback_url,language_query_param=:language_query_param,title=:title,description=:description,status=:status,smart_type=:smart_type,expires_at=:expires_at,max_clicks=:max_clicks,utm_source=:utm_source,utm_medium=:utm_medium,utm_campaign=:utm_campaign,utm_term=:utm_term,utm_content=:utm_content,campaign_id=:campaign_id,landing_page_id=:landing_page_id,updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(array_merge($data, ['tenant_id' => $tenantId, 'id' => $linkId]));
    }
}
