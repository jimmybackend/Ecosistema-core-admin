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
            'send_dry_run' => false,
            'send_write' => false,
            'smtp_connection' => false,
            'db_writes' => false,
            'mode' => 'read-only',
        ];
    }
}
