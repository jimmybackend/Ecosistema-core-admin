<?php

declare(strict_types=1);

namespace App\Core\Mail;

final readonly class MailAttachmentService
{
    public function __construct(private MailAttachmentRepository $attachments)
    {
    }

    public function listMessageAttachments(int $tenantId, int $userId, int $messageId): array
    {
        return $this->attachments->listLogicalByMessageForUser($tenantId, $userId, $messageId, 100);
    }
}
