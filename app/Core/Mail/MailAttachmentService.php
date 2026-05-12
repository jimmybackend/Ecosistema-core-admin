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

    public function listAvailableCloudFiles(int $tenantId, int $userId): array
    {
        return $this->attachments->listAvailableCloudFilesForUser($tenantId, $userId, 200);
    }

    public function replaceMessageAttachments(int $tenantId, int $userId, int $messageId, array $selectedFileIds): array
    {
        return $this->attachments->replaceMessageAttachments($tenantId, $userId, $messageId, $selectedFileIds);
    }
}
