<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

final class EcosistemaMailNotificationsAdapter
{
    public function capabilities(): array
    {
        return [
            'notification_templates_read' => true,
            'url_message_templates_read' => true,
            'queue_read' => true,
            'preview_dry_run' => true,
            'send_dry_run' => true,
            'send_write' => $this->mailSendEnabled(),
            'smtp_connection' => $this->smtpEnabled(),
            'queue_write' => $this->mailNotificationsEnabled(),
            'db_writes' => $this->mailNotificationsEnabled(),
            'mode' => $this->mailNotificationsEnabled() ? 'admin-controlled-write' : 'read-only',
        ];
    }

    private function mailNotificationsEnabled(): bool
    {
        return filter_var((string) getenv('ECOSISTEMA_MAIL_NOTIFICATIONS_ENABLED'), FILTER_VALIDATE_BOOL);
    }

    private function mailSendEnabled(): bool
    {
        return $this->mailNotificationsEnabled() && filter_var((string) getenv('ECOSISTEMA_MAIL_SEND_ENABLED'), FILTER_VALIDATE_BOOL);
    }

    private function smtpEnabled(): bool
    {
        return $this->mailSendEnabled() && filter_var((string) getenv('ECOSISTEMA_SMTP_ENABLED'), FILTER_VALIDATE_BOOL);
    }
}
