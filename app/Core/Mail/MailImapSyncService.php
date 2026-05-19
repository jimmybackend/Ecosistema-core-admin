<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Support\SecretBox;
use PDO;
use Throwable;

final readonly class MailImapSyncService
{
    public function __construct(private PDO $pdo, private SecretBox $secretBox)
    {
    }

    public function syncForUser(int $tenantId, int $userId, int $smtpAccountId, int $limit = 25): array
    {
        if (!extension_loaded('imap')) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'errors' => ['PHP IMAP extension not loaded.']];
        }

        $account = $this->findAuthorizedAccount($tenantId, $userId, $smtpAccountId);
        if ($account === null) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'errors' => ['Cuenta SMTP/IMAP no autorizada o inactiva.']];
        }

        $mailboxId = (int) ($account['mailbox_id'] ?? 0);
        $effectiveUserId = (int) ($account['mailbox_user_id'] ?? 0) > 0 ? (int) $account['mailbox_user_id'] : $userId;
        $folderId = $this->findInboxFolderId($tenantId, $mailboxId);
        $password = $this->secretBox->decrypt((string) ($account['password_encrypted'] ?? ''));
        if (trim($password) === '') {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'errors' => ['No se pudo resolver la contraseña IMAP.']];
        }

        $limit = max(1, min(250, $limit));
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $imap = null;

        try {
            $mailboxPath = $this->imapMailboxPath((string) ($account['host_in'] ?? ''), (int) ($account['port_in'] ?? 993), (string) ($account['ssl_in'] ?? 'ssl'));
            $imap = @imap_open($mailboxPath, (string) ($account['username'] ?? ''), $password, 0, 1);
            if ($imap === false) {
                $errors[] = 'No se pudo conectar al servidor IMAP.';
                return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'errors' => $errors];
            }

            $ids = imap_search($imap, 'ALL', SE_UID);
            if (!is_array($ids) || $ids === []) {
                return ['ok' => true, 'imported' => 0, 'skipped' => 0, 'errors' => []];
            }
            rsort($ids, SORT_NUMERIC);
            $ids = array_slice($ids, 0, $limit);

            foreach ($ids as $uid) {
                $uid = (int) $uid;
                $overview = imap_fetch_overview($imap, (string) $uid, FT_UID);
                if (!is_array($overview) || !isset($overview[0])) { $skipped++; continue; }
                $ov = $overview[0];

                $headers = (string) (imap_fetchheader($imap, (string) $uid, FT_UID) ?: '');
                $msgNo = imap_msgno($imap, $uid);
                $externalId = $this->extractMessageId((string) ($ov->message_id ?? ''), $headers);
                if ($externalId === null) {
                    $externalId = 'imap-uid:' . $uid;
                }

                if ($this->existsMessage($tenantId, $mailboxId, $externalId)) { $skipped++; continue; }

                $structure = imap_fetchstructure($imap, (string) $uid, FT_UID);
                $hasAttachments = $this->hasAttachments($structure) ? 1 : 0;
                $plainBody = $this->fetchBodyBySubtype($imap, $uid, 'PLAIN');
                $htmlBody = $this->fetchBodyBySubtype($imap, $uid, 'HTML');
                if ($plainBody === null) {
                    $plainBody = trim(strip_tags((string) $htmlBody));
                }

                $from = $this->extractAddressList((string) ($ov->from ?? ''));
                $to = $this->extractAddressList((string) ($ov->to ?? ''));
                $cc = $this->extractAddressList((string) ($ov->cc ?? ''));
                $bcc = $this->extractAddressList((string) ($ov->bcc ?? ''));

                $receivedAt = isset($ov->date) ? date('Y-m-d H:i:s', strtotime((string) $ov->date)) : date('Y-m-d H:i:s');
                $isRead = isset($ov->seen) && ((string) $ov->seen === '1' || (string) $ov->seen === 'S') ? 1 : 0;

                $insertedId = $this->insertMessage([
                    'tenant_id' => $tenantId,
                    'mailbox_id' => $mailboxId,
                    'folder_id' => $folderId,
                    'user_id' => $effectiveUserId,
                    'message_uuid' => $this->uuidV4(),
                    'mailbox_message_no' => $msgNo > 0 ? $msgNo : null,
                    'external_provider' => 'imap',
                    'external_message_id' => $externalId,
                    'direction' => 'inbound',
                    'mail_scope' => 'normal',
                    'from_address' => $from[0] ?? ((string) ($ov->from ?? '')),
                    'to_addresses' => json_encode($to, JSON_UNESCAPED_UNICODE),
                    'cc_addresses' => $cc === [] ? null : json_encode($cc, JSON_UNESCAPED_UNICODE),
                    'bcc_addresses' => $bcc === [] ? null : json_encode($bcc, JSON_UNESCAPED_UNICODE),
                    'subject' => isset($ov->subject) ? imap_utf8((string) $ov->subject) : null,
                    'body_text' => $plainBody,
                    'body_html' => $htmlBody,
                    'raw_headers' => $headers,
                    'has_attachments' => $hasAttachments,
                    'is_read' => $isRead,
                    'is_draft' => 0,
                    'is_spam' => 0,
                    'is_deleted' => 0,
                    'received_at' => $receivedAt,
                ]);

                if ($insertedId > 0) {
                    $this->insertRecipients($insertedId, $tenantId, $mailboxId, $to, 'to');
                    $this->insertRecipients($insertedId, $tenantId, $mailboxId, $cc, 'cc');
                    $this->insertRecipients($insertedId, $tenantId, $mailboxId, $bcc, 'bcc');
                    $imported++;
                } else {
                    $skipped++;
                }
            }
        } catch (Throwable) {
            $errors[] = 'Falló la sincronización IMAP.';
        } finally {
            if (is_resource($imap) || $imap instanceof \IMAP\Connection) {
                @imap_close($imap);
            }
        }

        return ['ok' => $errors === [], 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function findAuthorizedAccount(int $tenantId, int $userId, int $smtpAccountId): ?array
    {
        $sql = 'SELECT s.*, m.id AS mailbox_id, m.user_id AS mailbox_user_id
                FROM mail_smtp_accounts s
                INNER JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id
                WHERE s.id = :id
                  AND s.tenant_id = :tenant_id
                  AND s.status = :status
                  AND (s.created_by_user_id = :user_id OR m.user_id = :user_id OR s.available_to_everyone = 1 OR m.available_to_everyone = 1)
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $smtpAccountId, ':tenant_id' => $tenantId, ':status' => 'active', ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
    private function findInboxFolderId(int $tenantId, int $mailboxId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mail_folders WHERE tenant_id = :tenant_id AND mailbox_id = :mailbox_id AND system_name = :system_name ORDER BY id ASC LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':system_name' => 'inbox']);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }
    private function existsMessage(int $tenantId, int $mailboxId, string $externalMessageId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mail_messages WHERE tenant_id = :tenant_id AND mailbox_id = :mailbox_id AND external_provider = :provider AND external_message_id = :external_message_id LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':provider' => 'imap', ':external_message_id' => $externalMessageId]);
        return $stmt->fetchColumn() !== false;
    }
    private function insertMessage(array $data): int
    {
        $sql = 'INSERT INTO mail_messages (tenant_id, mailbox_id, folder_id, user_id, message_uuid, mailbox_message_no, external_provider, external_message_id, direction, mail_scope, from_address, to_addresses, cc_addresses, bcc_addresses, subject, body_text, body_html, raw_headers, has_attachments, is_read, is_draft, is_spam, is_deleted, received_at, created_at, updated_at)
                VALUES (:tenant_id, :mailbox_id, :folder_id, :user_id, :message_uuid, :mailbox_message_no, :external_provider, :external_message_id, :direction, :mail_scope, :from_address, :to_addresses, :cc_addresses, :bcc_addresses, :subject, :body_text, :body_html, :raw_headers, :has_attachments, :is_read, :is_draft, :is_spam, :is_deleted, :received_at, NOW(), NOW())';
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute($data);
        return $ok ? (int) $this->pdo->lastInsertId() : 0;
    }
    private function insertRecipients(int $messageId, int $tenantId, int $mailboxId, array $emails, string $type): void
    {
        if ($emails === []) { return; }
        $sql = 'INSERT INTO mail_message_recipients (tenant_id, mailbox_id, message_id, recipient_type, email, created_at) VALUES (:tenant_id,:mailbox_id,:message_id,:recipient_type,:email,NOW())';
        $stmt = $this->pdo->prepare($sql);
        foreach ($emails as $email) {
            if (!is_string($email) || trim($email) === '') { continue; }
            $stmt->execute([':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':message_id' => $messageId, ':recipient_type' => $type, ':email' => $email]);
        }
    }
    private function imapMailboxPath(string $host, int $port, string $ssl): string
    {
        $flags = '/imap';
        if ($ssl !== '' && strtolower($ssl) !== 'none') { $flags .= '/' . strtolower($ssl); } else { $flags .= '/notls'; }
        return '{' . $host . ':' . $port . $flags . '}INBOX';
    }
    private function extractMessageId(string $messageId, string $headers): ?string
    {
        $trim = trim($messageId);
        if ($trim !== '') { return $trim; }
        if (preg_match('/^Message-ID:\s*(.+)$/mi', $headers, $m) === 1) { return trim($m[1]); }
        return null;
    }
    private function extractAddressList(string $raw): array
    {
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $raw, $m);
        return array_values(array_unique(array_map('strtolower', $m[0] ?? [])));
    }
    private function hasAttachments(mixed $structure): bool
    {
        if (!is_object($structure)) { return false; }
        if (!empty($structure->parts) && is_array($structure->parts)) {
            foreach ($structure->parts as $part) {
                if (!empty($part->ifdparameters) || !empty($part->ifparameters)) { return true; }
            }
        }
        return false;
    }
    private function fetchBodyBySubtype($imap, int $uid, string $subtype): ?string
    {
        $structure = imap_fetchstructure($imap, (string) $uid, FT_UID);
        if (!is_object($structure) || !isset($structure->parts) || !is_array($structure->parts)) { return null; }
        foreach ($structure->parts as $index => $part) {
            if (strtoupper((string) ($part->subtype ?? '')) !== $subtype) { continue; }
            $partNo = (string) ($index + 1);
            $body = imap_fetchbody($imap, (string) $uid, $partNo, FT_UID);
            if (!is_string($body)) { continue; }
            $encoding = (int) ($part->encoding ?? 0);
            if ($encoding === 3) { $body = base64_decode($body, true) ?: ''; }
            if ($encoding === 4) { $body = quoted_printable_decode($body); }
            return trim($body);
        }
        return null;
    }
    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
