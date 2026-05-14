<?php
declare(strict_types=1);

namespace App\Core\BrowserAnalytics;

use PDO;

final readonly class EcosistemaBrowserAnalyticsCollectorRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createOrUpdateSession(int $tenantId, array $data): int
    {
        $sql = 'INSERT INTO browser_analytics_sessions (
            tenant_id,user_id,core_session_id,browser_session_uuid,visitor_uuid,started_at,last_activity_at,entry_url,exit_url,referrer_url,ip_address,user_agent,device_type,browser_name,browser_version,os_name,os_version,country,region,city,latitude,longitude,utm_source,utm_medium,utm_campaign,utm_term,utm_content,consent_status,created_at,updated_at
        ) VALUES (
            :tenant_id,:user_id,:core_session_id,:browser_session_uuid,:visitor_uuid,:started_at,:last_activity_at,:entry_url,:exit_url,:referrer_url,:ip_address,:user_agent,:device_type,:browser_name,:browser_version,:os_name,:os_version,:country,:region,:city,:latitude,:longitude,:utm_source,:utm_medium,:utm_campaign,:utm_term,:utm_content,:consent_status,NOW(),NOW()
        ) ON DUPLICATE KEY UPDATE
            user_id=VALUES(user_id),last_activity_at=VALUES(last_activity_at),exit_url=VALUES(exit_url),referrer_url=VALUES(referrer_url),ip_address=VALUES(ip_address),user_agent=VALUES(user_agent),device_type=VALUES(device_type),browser_name=VALUES(browser_name),browser_version=VALUES(browser_version),os_name=VALUES(os_name),os_version=VALUES(os_version),country=VALUES(country),region=VALUES(region),city=VALUES(city),latitude=VALUES(latitude),longitude=VALUES(longitude),utm_source=VALUES(utm_source),utm_medium=VALUES(utm_medium),utm_campaign=VALUES(utm_campaign),utm_term=VALUES(utm_term),utm_content=VALUES(utm_content),consent_status=VALUES(consent_status),updated_at=NOW()';

        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function findSessionIdByUuid(int $tenantId, string $browserSessionUuid): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM browser_analytics_sessions WHERE tenant_id=:tenant_id AND browser_session_uuid=:browser_session_uuid LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':browser_session_uuid', $browserSessionUuid);
        $stmt->execute();

        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public function insertPageview(int $tenantId, array $data): int
    {
        $sql = 'INSERT INTO browser_analytics_pageviews (tenant_id,session_id,user_id,campaign_id,landing_page_id,short_link_id,crm_lead_id,page_url,page_title,referrer_url,path,query_string,hash_fragment,viewed_at,duration_ms,scroll_depth_percent,is_landing_view,is_campaign_view,meta_json)
                VALUES (:tenant_id,:session_id,:user_id,:campaign_id,:landing_page_id,:short_link_id,:crm_lead_id,:page_url,:page_title,:referrer_url,:path,:query_string,:hash_fragment,:viewed_at,:duration_ms,:scroll_depth_percent,:is_landing_view,:is_campaign_view,:meta_json)';
        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    public function insertEvent(int $tenantId, array $data): int
    {
        $sql = 'INSERT INTO browser_analytics_events (tenant_id,session_id,pageview_id,user_id,campaign_id,landing_page_id,short_link_id,crm_lead_id,event_type,event_name,element_id,element_text,element_url,value_numeric,value_text,score_points,metadata_json,occurred_at)
                VALUES (:tenant_id,:session_id,:pageview_id,:user_id,:campaign_id,:landing_page_id,:short_link_id,:crm_lead_id,:event_type,:event_name,:element_id,:element_text,:element_url,:value_numeric,:value_text,:score_points,:metadata_json,:occurred_at)';
        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }
}
