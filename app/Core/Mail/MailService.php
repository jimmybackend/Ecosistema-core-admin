<?php

declare(strict_types=1);

namespace App\Core\Mail;

final readonly class MailService
{
    public function __construct(private MailboxRepository $mailboxes, private MailMessageRepository $messages)
    {
    }

    public function listMessages(int $tenantId, int $userId): array { return $this->messages->listByUser($tenantId, $userId, 100); }
    public function findMessage(int $tenantId, int $userId, int $id): ?array { return $this->messages->findByIdForUser($tenantId, $userId, $id); }
    public function listActiveMailboxes(int $tenantId, int $userId): array { return $this->mailboxes->listActiveByUser($tenantId, $userId); }

    public function createDraft(int $tenantId, int $userId, array $input): string
    {
        $mailboxId = (int) ($input['mailbox_id'] ?? 0);
        $mailbox = $this->mailboxes->findActiveById($tenantId, $userId, $mailboxId);
        if ($mailbox === null) { return 'No hay mailbox activo disponible para crear borradores.'; }

        $toAddresses = $this->parseAddressList((string) ($input['to_addresses'] ?? ''));
        if ($toAddresses === []) { return 'No se pudo guardar el borrador.'; }
        $cc = $this->parseAddressList((string) ($input['cc_addresses'] ?? ''));
        $bcc = $this->parseAddressList((string) ($input['bcc_addresses'] ?? ''));

        $ok = $this->messages->createDraft([
            ':tenant_id' => $tenantId,
            ':mailbox_id' => $mailboxId,
            ':folder_id' => $this->mailboxes->findDraftFolderId($tenantId, $mailboxId),
            ':user_id' => $userId,
            ':message_uuid' => $this->uuidV4(),
            ':direction' => 'outbound',
            ':mail_scope' => 'normal',
            ':from_address' => (string) ($mailbox['full_address'] ?? ''),
            ':to_addresses' => json_encode($toAddresses, JSON_UNESCAPED_UNICODE),
            ':cc_addresses' => $cc === [] ? null : json_encode($cc, JSON_UNESCAPED_UNICODE),
            ':bcc_addresses' => $bcc === [] ? null : json_encode($bcc, JSON_UNESCAPED_UNICODE),
            ':subject' => $this->nullable((string) ($input['subject'] ?? '')),
            ':body_text' => $this->nullable((string) ($input['body_text'] ?? '')),
            ':body_html' => null,
            ':has_attachments' => 0,
            ':is_read' => 1,
            ':is_draft' => 1,
            ':is_deleted' => 0,
        ]);

        return $ok ? 'Borrador creado correctamente.' : 'No se pudo guardar el borrador.';
    }

    public function updateRead(int $tenantId, int $userId, int $id): string { return $this->messages->markReadToggle($tenantId, $userId, $id) ? 'Mensaje actualizado correctamente.' : 'Mensaje no encontrado.'; }
    public function updateStar(int $tenantId, int $userId, int $id): string { return $this->messages->toggleStar($tenantId, $userId, $id) ? 'Mensaje actualizado correctamente.' : 'Mensaje no encontrado.'; }
    public function trash(int $tenantId, int $userId, int $id): string { return $this->messages->moveToTrash($tenantId, $userId, $id) ? 'Mensaje enviado a papelera.' : 'Mensaje no encontrado.'; }

    private function parseAddressList(string $raw): array
    {
        $items = array_values(array_filter(array_map('trim', explode(',', $raw)), static fn (string $v): bool => $v !== ''));
        return array_values(array_filter($items, static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
    }

    private function nullable(string $value): ?string { $trim = trim($value); return $trim === '' ? null : $trim; }
    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
