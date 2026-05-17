<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

use PDO;

final readonly class EcosistemaSendNotificationService
{
    public function __construct(private PDO $pdo, private EcosistemaSendNotificationRepository $repository, private EcosistemaMailNotificationsAdapter $adapter)
    {
    }

    public function sendControlled(int $tenantId, int $authUserId, array $input): array
    {
        $caps = $this->adapter->capabilities();
        if (empty($caps['queue_write'])) {
            return $this->error('queue_write deshabilitado por flags.');
        }

        $templateId = (int) ($input['template_id'] ?? 0);
        $recipientUserId = (int) ($input['recipient_user_id'] ?? 0);
        $mailboxId = (int) ($input['mailbox_id'] ?? 0);
        if ($templateId <= 0 || $recipientUserId <= 0 || $mailboxId <= 0) {
            return $this->error('template_id, recipient_user_id y mailbox_id son obligatorios.');
        }

        $template = $this->repository->findActiveTemplate($tenantId, $templateId);
        if ($template === null) {
            return $this->error('Template no encontrado para el tenant actual.');
        }

        $recipient = $this->repository->findRecipientUser($tenantId, $recipientUserId);
        if ($recipient === null) {
            return $this->error('Destinatario inválido para tenant actual.');
        }

        $smtp = $this->repository->findSmtpAccount($tenantId, $mailboxId);
        if ($smtp === null || strtolower((string) ($smtp['status'] ?? '')) !== 'active') {
            return $this->error('SMTP account no disponible/activa para mailbox seleccionado.');
        }

        $payloadJson = trim((string) ($input['payload_json'] ?? '{}'));
        $subject = $this->safePreview((string) ($template['subject'] ?? ''), 180);
        $bodyPreview = $this->safePreview((string) ($template['body'] ?? ''), 2000);
        $toAddress = (string) ($recipient['email'] ?? '');
        if ($toAddress === '' || filter_var($toAddress, FILTER_VALIDATE_EMAIL) === false) {
            return $this->error('Email del destinatario no válido.');
        }

        $smtpWrite = !empty($caps['send_write']) && !empty($caps['smtp_connection']);
        $queueId = 0;
        $mailMessageId = 0;
        $deliveryLogId = 0;

        $this->pdo->beginTransaction();
        try {
            $queueId = $this->repository->createQueueItem([
                ':tenant_id' => $tenantId,
                ':user_id' => (int) $recipient['id'],
                ':channel_id' => (int) $template['channel_id'],
                ':template_id' => (int) $template['id'],
                ':module_code' => 'core-admin',
                ':entity_table' => 'notifications_templates',
                ':entity_id' => (int) $template['id'],
                ':title' => $subject,
                ':body' => $bodyPreview,
                ':payload_json' => $payloadJson,
                ':status' => $smtpWrite ? 'pending' : 'canceled',
                ':scheduled_at' => date('Y-m-d H:i:s'),
            ]);

            if ($smtpWrite) {
                $mailMessageId = $this->repository->createMailMessage([
                    ':tenant_id' => $tenantId,
                    ':mailbox_id' => $mailboxId,
                    ':folder_id' => null,
                    ':user_id' => $authUserId,
                    ':source_module' => 'mail_notifications',
                    ':source_table' => 'notifications_queue',
                    ':source_id' => $queueId,
                    ':message_uuid' => $this->uuidv4(),
                    ':direction' => 'outbound',
                    ':mail_scope' => 'system',
                    ':from_address' => (string) ($smtp['username'] ?? ''),
                    ':to_addresses' => json_encode([$toAddress], JSON_UNESCAPED_UNICODE),
                    ':subject' => $subject,
                    ':body_text' => $bodyPreview,
                    ':body_html' => '',
                ]);

                $deliveryLogId = $this->repository->createDeliveryLog([
                    ':tenant_id' => $tenantId,
                    ':message_id' => $mailMessageId,
                    ':smtp_account_id' => (int) $smtp['id'],
                    ':provider' => 'smtp',
                    ':status' => 'queued',
                    ':response_code' => null,
                    ':response_message' => 'SMTP send pendiente por worker controlado.',
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return $this->error('No se pudo preparar el envío controlado.');
        }

        return [
            'ok' => true,
            'queue_created' => $queueId > 0,
            'send_executed' => false,
            'smtp_connection' => $smtpWrite,
            'queue_id' => $queueId,
            'mail_message_id' => $mailMessageId > 0 ? $mailMessageId : null,
            'mail_delivery_log_id' => $deliveryLogId > 0 ? $deliveryLogId : null,
            'recipient_masked' => $this->maskEmail($toAddress),
            'subject_preview' => $subject,
            'status' => $smtpWrite ? 'queued' : 'blocked_by_flags',
        ];
    }

    private function safePreview(string $value, int $max): string { $clean=trim(strip_tags($value)); $head=mb_substr($clean,0,$max); return $head===$clean?$head:($head.'…'); }
    private function error(string $message): array { return ['ok'=>false,'error'=>$message,'queue_created'=>false,'send_executed'=>false,'smtp_connection'=>false]; }
    private function maskEmail(string $email): string { if(!str_contains($email,'@')){return '***';} [$l,$d]=explode('@',$email,2); return mb_substr($l,0,1).'***@'.$d; }
    private function uuidv4(): string { $d=random_bytes(16); $d[6]=chr((ord($d[6])&0x0f)|0x40); $d[8]=chr((ord($d[8])&0x3f)|0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($d),4)); }
}
