<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

use PDO;

final readonly class EcosistemaSendNotificationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveTemplate(int $tenantId, int $templateId): ?array
    {
        $sql = 'SELECT nt.id,nt.tenant_id,nt.channel_id,nt.subject,nt.body,nc.code AS channel_code FROM notifications_templates nt INNER JOIN notifications_channels nc ON nc.id=nt.channel_id WHERE nt.tenant_id=:tenant_id AND nt.id=:id AND nt.is_active=1 LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':id' => $templateId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function findSmtpAccount(int $tenantId, int $mailboxId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,mailbox_id,status,host_out,port_out,ssl_out,username,password_encrypted FROM mail_smtp_accounts WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function findRecipientUser(int $tenantId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,email,status FROM core_users WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function createQueueItem(array $payload): int
    {
        $sql = 'INSERT INTO notifications_queue (tenant_id,user_id,channel_id,template_id,module_code,entity_table,entity_id,title,body,payload_json,status,scheduled_at,created_at) VALUES (:tenant_id,:user_id,:channel_id,:template_id,:module_code,:entity_table,:entity_id,:title,:body,:payload_json,:status,:scheduled_at,NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($payload);

        return (int) $this->pdo->lastInsertId();
    }

    public function createMailMessage(array $payload): int
    {
        $sql = 'INSERT INTO mail_messages (tenant_id,mailbox_id,folder_id,user_id,source_module,source_table,source_id,message_uuid,direction,mail_scope,from_address,to_addresses,subject,body_text,body_html,has_attachments,is_read,is_deleted,received_at,sent_at,created_at,updated_at) VALUES (:tenant_id,:mailbox_id,:folder_id,:user_id,:source_module,:source_table,:source_id,:message_uuid,:direction,:mail_scope,:from_address,:to_addresses,:subject,:body_text,:body_html,0,0,0,NOW(),NOW(),NOW(),NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($payload);

        return (int) $this->pdo->lastInsertId();
    }

    public function createDeliveryLog(array $payload): int
    {
        $sql = 'INSERT INTO mail_delivery_logs (tenant_id,message_id,smtp_account_id,provider,status,response_code,response_message,attempted_at) VALUES (:tenant_id,:message_id,:smtp_account_id,:provider,:status,:response_code,:response_message,NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($payload);

        return (int) $this->pdo->lastInsertId();
    }
}
