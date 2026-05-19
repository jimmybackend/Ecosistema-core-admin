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
            if (is_array($mailboxSmtp)) return $this->fromAccount($mailboxSmtp, 'mailbox_smtp');
        }

        $tenantSmtp = $this->smtpAccounts->findActiveByUserFallback($tenantId, $userId);
        if (is_array($tenantSmtp)) return $this->fromAccount($tenantSmtp, 'tenant_smtp');

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

        return [
            'source' => $source,
            'status' => (string) ($account['status'] ?? 'inactive'),
            'config' => ['host' => (string) ($account['host_out'] ?? ''), 'port' => (int) ($account['port_out'] ?? 0), 'encryption' => (string) ($account['ssl_out'] ?? ''), 'username' => $username, 'password' => $password],
            'safe' => ['source' => $source, 'status' => (string) ($account['status'] ?? 'inactive'), 'host' => (string) ($account['host_out'] ?? ''), 'port' => (int) ($account['port_out'] ?? 0), 'encryption' => (string) ($account['ssl_out'] ?? ''), 'username_masked' => $this->mask($username), 'email_address_masked' => $this->maskEmail($email)],
        ];
    }

    private function mask(string $value): string { if ($value === '') return 'no-configurado'; $len=strlen($value); if ($len<=2) return str_repeat('*',$len); return substr($value,0,1).str_repeat('*',$len-2).substr($value,-1); }
    private function maskEmail(string $email): string { if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) return $this->mask($email); [$l,$d]=explode('@',$email,2); return $this->mask($l).'@'.$d; }
}
