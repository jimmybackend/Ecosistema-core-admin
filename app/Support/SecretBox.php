<?php

declare(strict_types=1);

namespace App\Support;

final class SecretBox
{
    public function encrypt(string $plain): string
    {
        $key = $this->key();
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (!is_string($cipher)) {
            throw new \RuntimeException('No se pudo cifrar secreto SMTP.');
        }

        return base64_encode($iv . $tag . $cipher);
    }

    public function decrypt(string $payload): string
    {
        $key = $this->key();
        $raw = base64_decode($payload, true);
        if (!is_string($raw) || strlen($raw) < 28) {
            throw new \RuntimeException('Secreto SMTP inválido.');
        }
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (!is_string($plain)) {
            throw new \RuntimeException('No se pudo descifrar secreto SMTP.');
        }

        return $plain;
    }

    private function key(): string
    {
        $appKey = trim((string) Env::get('APP_KEY', ''));
        if ($appKey === '') {
            throw new \RuntimeException('APP_KEY missing from runtime config');
        }
        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            if (is_string($decoded) && strlen($decoded) >= 32) {
                return substr($decoded, 0, 32);
            }
        }

        return substr(hash('sha256', $appKey, true), 0, 32);
    }
}
