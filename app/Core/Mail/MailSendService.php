<?php

declare(strict_types=1);

namespace App\Core\Mail;

final readonly class MailSendService
{
    public function __construct(private MailMessageRepository $messages, private MailAttachmentService $attachments, private MailConfig $mailConfig, private MailOutgoingAttachmentService $outgoingAttachments, private ?MailSender $sender = null)
    {
    }

    public function previewDraftSend(int $tenantId, int $userId, int $messageId): array
    {
        $message = $this->messages->findByIdForUser($tenantId, $userId, $messageId);
        if ($message === null) return ['ok'=>false,'reason'=>'Mensaje no encontrado.'];
        if ((int)($message['is_deleted'] ?? 0) === 1) return ['ok'=>false,'reason'=>'El mensaje está en papelera o eliminado.'];
        if ((int)($message['is_draft'] ?? 0) !== 1) return ['ok'=>false,'reason'=>'Sólo se puede preparar envío de borradores.'];
        $recipients = $this->extractRecipients($message);
        if ($recipients === []) return ['ok'=>false,'reason'=>'El borrador no tiene destinatarios válidos.'];
        if (count($recipients) > 10) return ['ok'=>false,'reason'=>'El borrador supera el máximo de 10 destinatarios.'];
        $smtp = $this->mailConfig->toSafeArray();
        $resolved = $this->outgoingAttachments->resolveForSend($tenantId, $userId, $messageId);
        $ready = (bool)($smtp['send_enabled']??false) && (bool)($smtp['allow_test_send']??false) && (bool)($smtp['is_valid']??false) && (bool)($resolved['ok']??false);
        return ['ok'=>true,'message'=>$message,'recipients'=>$recipients,'subject'=>trim((string)($message['subject'] ?? '')),'body_text_preview'=>mb_substr(trim((string)($message['body_text'] ?? '')),0,500),'smtp'=>$smtp,'ready'=>$ready,'reason'=>$ready?'Listo para envío individual controlado.':(($resolved['ok']??false)?'Revisar configuración SMTP para habilitar envío.':'Adjuntos bloquean el envío.'),'attachments'=>$resolved['attachments']??[],'attachments_summary'=>$resolved,'can_send_real'=>$ready];
    }
    public function sendDraft(int $tenantId, int $userId, int $messageId): array { $preview=$this->previewDraftSend($tenantId,$userId,$messageId); if(($preview['ok']??false)!==true)return['ok'=>false,'action'=>'mail.send_failed','reason'=>(string)($preview['reason']??'No se pudo preparar el envío.')]; $smtp=(array)($preview['smtp']??[]); if(!(bool)($smtp['send_enabled']??false)||!(bool)($smtp['allow_test_send']??false)) return ['ok'=>false,'action'=>'mail.send_failed','reason'=>'El envío está bloqueado por configuración SMTP.']; if(!(bool)($smtp['is_valid']??false)) return ['ok'=>false,'action'=>'mail.send_failed','reason'=>'La configuración SMTP no es válida.']; $summary=(array)($preview['attachments_summary']??[]); if(!(bool)($summary['ok']??false)) return ['ok'=>false,'action'=>'mail.send_blocked_by_attachments','reason'=>'Adjuntos inválidos bloquean envío completo.','attachment_count'=>(int)($summary['count']??0),'attachment_total_bytes'=>(int)($summary['total_bytes']??0)]; $payload=['from'=>(string)(($preview['message']['from_address'] ?? '')),'to'=>(array)($preview['recipients']??[]),'subject'=>(string)($preview['subject']??''),'body'=>(string)(($preview['message']['body_text'] ?? '')),'attachments'=>[]]; foreach((array)($preview['attachments']??[]) as $a){ if(!empty($a['ready']) && is_string($a['path']??null)) $payload['attachments'][]=['name'=>(string)$a['name'],'mime_type'=>(string)$a['mime_type'],'path'=>(string)$a['path']]; } $r=$this->resolveSender()->send($payload); if(($r['sent']??false)!==true)return['ok'=>false,'action'=>'mail.send_failed','reason'=>(string)($r['message']??'Falló el envío SMTP.')]; $this->messages->markDraftAsSent($tenantId,$userId,$messageId); return ['ok'=>true,'action'=>'mail.sent','reason'=>'Envío individual ejecutado correctamente.','attachment_count'=>(int)($summary['count']??0),'attachment_total_bytes'=>(int)($summary['total_bytes']??0)]; }
    public function canSendReal(array $preview): bool { return (bool)($preview['can_send_real']??false); }
    private function resolveSender(): MailSender { return $this->sender ?? new SmtpMailer($this->mailConfig->senderConfig()); }
    private function extractRecipients(array $message): array { $all=[]; foreach(['to_addresses','cc_addresses','bcc_addresses'] as $f){$d=json_decode((string)($message[$f]??''),true); if(!is_array($d)) continue; foreach($d as $e){$c=trim((string)$e); if($c!==''&&filter_var($c,FILTER_VALIDATE_EMAIL)!==false)$all[]=mb_strtolower($c);} } return array_values(array_unique($all)); }
}
