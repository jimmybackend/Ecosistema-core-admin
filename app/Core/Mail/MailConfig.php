<?php

declare(strict_types=1);

namespace App\Core\Mail;

final readonly class MailConfig
{
    public function __construct(private array $config)
    {
    }

    public function toSafeArray(): array
    {
        $from = is_array($this->config['from'] ?? null) ? $this->config['from'] : [];

        return [
            'mailer' => (string) ($this->config['mailer'] ?? 'smtp'),
            'host' => (string) ($this->config['host'] ?? ''),
            'port' => (int) ($this->config['port'] ?? 0),
            'encryption' => (string) ($this->config['encryption'] ?? ''),
            'username_masked' => $this->maskUsername((string) ($this->config['username'] ?? '')),
            'from_address' => (string) ($from['address'] ?? ''),
            'from_name' => (string) ($from['name'] ?? ''),
            'send_enabled' => (bool) ($this->config['send_enabled'] ?? false),
            'allow_test_send' => (bool) ($this->config['allow_test_send'] ?? false),
            'is_valid' => $this->isValid(),
            'validation_errors' => $this->validationErrors(),
        ];
    }

    public function isValid(): bool
    {
        return $this->validationErrors() === [];
    }

    public function senderConfig(): array
    {
        return [
            'host' => (string) ($this->config['host'] ?? ''),
            'port' => (int) ($this->config['port'] ?? 0),
            'encryption' => (string) ($this->config['encryption'] ?? ''),
            'username' => (string) ($this->config['username'] ?? ''),
            'password' => (string) ($this->config['password'] ?? ''),
        ];
    }

    public function validationErrors(): array
    {
        $errors = [];
        $host = trim((string) ($this->config['host'] ?? ''));
        $port = (int) ($this->config['port'] ?? 0);
        $encryption = strtolower(trim((string) ($this->config['encryption'] ?? '')));
        $from = is_array($this->config['from'] ?? null) ? $this->config['from'] : [];
        $fromAddress = trim((string) ($from['address'] ?? ''));

        if ($host === '') { $errors[] = 'MAIL_HOST es requerido.'; }
        if ($port <= 0) { $errors[] = 'MAIL_PORT debe ser mayor a 0.'; }
        if ($encryption !== '' && !in_array($encryption, ['tls', 'ssl'], true)) { $errors[] = 'MAIL_ENCRYPTION debe ser tls, ssl o vacío.'; }
        if ($fromAddress === '' || filter_var($fromAddress, FILTER_VALIDATE_EMAIL) === false) { $errors[] = 'MAIL_FROM_ADDRESS debe ser un correo válido.'; }

        return $errors;
    }

    private function maskUsername(string $username): string
    {
        if ($username === '') {
            return 'no-configurado';
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL) !== false) {
            [$local, $domain] = explode('@', $username, 2);
            return $this->maskFragment($local) . '@' . $domain;
        }

        return $this->maskFragment($username);
    }

    private function maskFragment(string $value): string
    {
        $len = strlen($value);
        if ($len <= 2) {
            return str_repeat('*', $len);
        }

        return substr($value, 0, 1) . str_repeat('*', $len - 2) . substr($value, -1);
    }
}
