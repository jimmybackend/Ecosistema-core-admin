<?php

declare(strict_types=1);

namespace App\Core\Landing;

use PDO;

final readonly class EcosistemaLandingFormSubmitRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createSubmission(int $tenantId, array $row): int
    {
        $sql = 'INSERT INTO landing_form_submissions (tenant_id,form_id,landing_page_id,campaign_id,visit_id,submitted_by_user_id,contact_name,email,phone,company_name,interest,message,raw_data_json,ip_address,user_agent,country,region,city,latitude,longitude,status,spam_score,submitted_at,processed_at) VALUES (:tenant_id,:form_id,:landing_page_id,:campaign_id,:visit_id,:submitted_by_user_id,:contact_name,:email,:phone,:company_name,:interest,:message,:raw_data_json,:ip_address,:user_agent,:country,:region,:city,:latitude,:longitude,:status,:spam_score,NOW(),NULL)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        foreach (['form_id','landing_page_id','campaign_id','visit_id','submitted_by_user_id','spam_score'] as $intKey) {
            $stmt->bindValue(':' . $intKey, $row[$intKey], PDO::PARAM_INT);
        }
        foreach (['contact_name','email','phone','company_name','interest','message','raw_data_json','ip_address','user_agent','country','region','city','status'] as $k) {
            $stmt->bindValue(':' . $k, (string)($row[$k] ?? ''), PDO::PARAM_STR);
        }
        $stmt->bindValue(':latitude', $row['latitude']);
        $stmt->bindValue(':longitude', $row['longitude']);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function insertSubmissionValue(int $tenantId, int $submissionId, array $value): void
    {
        $sql = 'INSERT INTO landing_form_submission_values (tenant_id,submission_id,field_id,field_key,field_label,value_text,value_json,file_path,s3_key,created_at) VALUES (:tenant_id,:submission_id,:field_id,:field_key,:field_label,:value_text,:value_json,:file_path,:s3_key,NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $stmt->bindValue(':field_id', (int)($value['field_id'] ?? 0), PDO::PARAM_INT);
        foreach (['field_key','field_label','value_text','value_json','file_path','s3_key'] as $k) {
            $stmt->bindValue(':' . $k, (string)($value[$k] ?? ''), PDO::PARAM_STR);
        }
        $stmt->execute();
    }
}
