<?php

declare(strict_types=1);

namespace App\Core\Mail;

final readonly class MailSendService
{
    public function __construct(
        private MailMessageRepository $messages,
        private MailAttachmentService $attachments,
        private MailConfig $mailConfig,
        private ?MailSender $sender = null,
    ) {
    }

    public function previewDraftSend(int $tenantId, int $userId, int $messageId): array
    {
        $message = $this->messages->findByIdForUser($tenantId, $userId, $messageId);
        if ($message === null) {
            return ['ok' => false, 'reason' => 'Mensaje no encontrado.'];
        }

        if ((int) ($message['is_deleted'] ?? 0) === 1) {
            return ['ok' => false, 'reason' => 'El mensaje está en papelera o eliminado.'];
        }

        if ((int) ($message['is_draft'] ?? 0) !== 1) {
            return ['ok' => false, 'reason' => 'Sólo se puede preparar envío de borradores.'];
        }

        $recipients = $this->extractRecipients($message);
        if ($recipients === []) {
            return ['ok' => false, 'reason' => 'El borrador no tiene destinatarios válidos.'];
        }

        if (count($recipients) > 10) {
            return ['ok' => false, 'reason' => 'El borrador supera el máximo de 10 destinatarios.'];
        }

        $subject = trim((string) ($message['subject'] ?? ''));
        $bodyText = trim((string) ($message['body_text'] ?? ''));
        if ($subject === '' && $bodyText === '') {
            return ['ok' => false, 'reason' => 'El borrador debe tener asunto o cuerpo.'];
        }

        $smtp = $this->mailConfig->toSafeArray();

        return [
            'ok' => true,
            'message' => $message,
            'recipients' => $recipients,
            'subject' => $subject,
            'body_text_preview' => mb_substr($bodyText, 0, 500),
            'smtp' => $smtp,
            'ready' => (bool) ($smtp['send_enabled'] ?? false),
            'reason' => ((bool) ($smtp['send_enabled'] ?? false))
                ? 'La configuración permite envío, pero este PR mantiene modo preparación (dry-run).'
                : 'El envío está deshabilitado por configuración.',
            'attachments' => $this->attachments->listMessageAttachments($tenantId, $userId, $messageId),
        ];
    }

    public function sendDraft(int $tenantId, int $userId, int $messageId): array
    {
        $preview = $this->previewDraftSend($tenantId, $userId, $messageId);
        if (($preview['ok'] ?? false) !== true) {
            return ['ok' => false, 'action' => 'mail.send_failed', 'reason' => (string) ($preview['reason'] ?? 'No se pudo preparar el envío.')];
        }

        $smtp = (array) ($preview['smtp'] ?? []);
        if (!(bool) ($smtp['send_enabled'] ?? false)) {
            return ['ok' => false, 'action' => 'mail.send_blocked_by_config', 'ready' => false, 'reason' => 'El envío está deshabilitado por configuración.'];
        }

        if (!(bool) ($smtp['allow_test_send'] ?? false)) {
            return ['ok' => false, 'action' => 'mail.send_blocked_by_config', 'ready' => false, 'reason' => 'MAIL_ALLOW_TEST_SEND=false. El envío de prueba está bloqueado.'];
        }

        if (!(bool) ($smtp['is_valid'] ?? false)) {
            return ['ok' => false, 'action' => 'mail.send_failed', 'ready' => false, 'reason' => 'La configuración SMTP no es válida.'];
        }

        $attachments = is_array($preview['attachments'] ?? null) ? $preview['attachments'] : [];
        if ($attachments !== []) {
            return ['ok' => false, 'action' => 'mail.send_blocked_attachments_pending', 'ready' => false, 'reason' => 'Adjuntos salientes aún no habilitados para envío binario seguro.'];
        }

        $message = (array) ($preview['message'] ?? []);
        $sendResult = $this->resolveSender()->send([
            'from' => (string) ($message['from_address'] ?? ''),
            'to' => (array) ($preview['recipients'] ?? []),
            'subject' => (string) ($preview['subject'] ?? ''),
            'body' => (string) ($message['body_text'] ?? ''),
        ]);
        if (($sendResult['sent'] ?? false) !== true) {
            return ['ok' => false, 'action' => 'mail.send_failed', 'ready' => true, 'reason' => (string) ($sendResult['message'] ?? 'Falló el envío SMTP.')];
        }

        $this->messages->markDraftAsSent($tenantId, $userId, $messageId);
        return ['ok' => true, 'action' => 'mail.sent', 'ready' => true, 'reason' => 'Envío individual ejecutado correctamente.'];
    }

    public function canSendReal(array $preview): bool
    {
        if (($preview['ok'] ?? false) !== true) {
            return false;
        }
        $smtp = (array) ($preview['smtp'] ?? []);
        return (bool) ($smtp['send_enabled'] ?? false) && (bool) ($smtp['allow_test_send'] ?? false) && (bool) ($smtp['is_valid'] ?? false);
    }

    private function resolveSender(): MailSender
    {
        return $this->sender ?? new SmtpMailer($this->mailConfig->senderConfig());
    }

    private function extractRecipients(array $message): array
    {
        $all = [];
        foreach (['to_addresses', 'cc_addresses', 'bcc_addresses'] as $field) {
            $decoded = json_decode((string) ($message[$field] ?? ''), true);
            if (!is_array($decoded)) {
                continue;
            }
            foreach ($decoded as $email) {
                $candidate = trim((string) $email);
                if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false) {
                    $all[] = mb_strtolower($candidate);
                }
            }
        }

        return array_values(array_unique($all));
    }
}
