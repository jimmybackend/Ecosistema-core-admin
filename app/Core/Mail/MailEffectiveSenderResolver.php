<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Support\SecretBox;

final readonly class MailEffectiveSenderResolver
{
    public function __construct(private MailConfig $globalConfig, private MailSmtpAccountRepository $smtpAccounts, private SecretBox $secretBox)
    {
    }

    public function resolve(int $tenantId, int $userId, ?int $mailboxId): array
    {
        if ($mailboxId !== null) {
            $mailboxSmtp = $this->smtpAccounts->findActiveByMailboxForUser($tenantId, $userId, $mailboxId);
            if (is_array($mailboxSmtp)) {
                return $this->fromAccount($mailboxSmtp, 'mailbox_smtp');
            }
        }

        $tenantSmtp = $this->smtpAccounts->findActiveByUserFallback($tenantId, $userId);
        if (is_array($tenantSmtp)) {
            return $this->fromAccount($tenantSmtp, 'tenant_smtp');
        }

        $safe = $this->globalConfig->toSafeArray();
        return ['source' => 'global_env', 'status' => ($safe['is_valid'] ?? false) ? 'ready' : 'invalid', 'config' => $this->globalConfig->senderConfig(), 'safe' => $safe];
    }

    private function fromAccount(array $account, string $source): array
    {
        $password = '';
        if (is_string($account['password_encrypted'] ?? null) && trim((string) $account['password_encrypted']) !== '') {
            $password = $this->secretBox->decrypt((string) $account['password_encrypted']);
        }

        $username = (string) ($account['username'] ?? '');
        $email = (string) ($account['email_address'] ?? '');
        $safe = [
            'source' => $source,
            'status' => (string) ($account['status'] ?? 'inactive'),
            'mailbox_id' => isset($account['mailbox_id']) ? (int) $account['mailbox_id'] : null,
            'mailbox_full_address' => (string) ($account['mailbox_full_address'] ?? ''),
            'smtp_account_id' => isset($account['id']) ? (int) $account['id'] : null,
            'account_name' => (string) ($account['name'] ?? ''),
            'email_address' => $email,
            'host_in' => (string) ($account['host_in'] ?? ''),
            'port_in' => (int) ($account['port_in'] ?? 0),
            'ssl_in' => (string) ($account['ssl_in'] ?? ''),
            'host_out' => (string) ($account['host_out'] ?? ''),
            'port_out' => (int) ($account['port_out'] ?? 0),
            'ssl_out' => (string) ($account['ssl_out'] ?? ''),
            'username_masked' => $this->mask($username),
            'max_daily_email' => (int) ($account['max_daily_email'] ?? 0),
            'enable_limit' => (int) ($account['enable_limit'] ?? 0),
            'available_to_everyone' => (int) ($account['available_to_everyone'] ?? 0),
            'password_encrypted_present' => trim((string) ($account['password_encrypted'] ?? '')) !== '' ? 'yes' : 'no',
            'last_error' => $this->sanitizeLastError((string) ($account['last_error'] ?? '')),
        ];

        return [
            'source' => $source,
            'status' => (string) ($account['status'] ?? 'inactive'),
            'config' => ['host' => (string) ($account['host_out'] ?? ''), 'port' => (int) ($account['port_out'] ?? 0), 'encryption' => (string) ($account['ssl_out'] ?? ''), 'username' => $username, 'password' => $password],
            'safe' => $safe,
        ];
    }

    private function sanitizeLastError(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');
    }

    private function mask(string $value): string { if ($value === '') return 'no-configurado'; $len=strlen($value); if ($len<=2) return str_repeat('*',$len); return substr($value,0,1).str_repeat('*',$len-2).substr($value,-1); }
}
