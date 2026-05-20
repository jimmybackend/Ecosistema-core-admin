<?php
declare(strict_types=1);

namespace App\Core\Cloud;

use DateTimeInterface;
use InvalidArgumentException;

final class CloudPath
{
    public static function normalizeRootPrefix(int $userId): string
    {
        return 'users/' . $userId . '/';
    }

    public static function joinS3Prefix(string ...$parts): string
    {
        $clean = [];
        foreach ($parts as $part) {
            $p = trim(str_replace('\\', '/', $part), '/');
            if ($p !== '') { $clean[] = $p; }
        }
        return implode('/', $clean) . '/';
    }

    public static function normalizeFolderPrefix(int $userId, string $prefix): string
    {
        $root = self::normalizeRootPrefix($userId);
        $n = self::joinS3Prefix($prefix);
        if (!str_starts_with($n, $root)) { $n = self::joinS3Prefix($root, $n); }
        self::assertSafeScope($userId, $n);
        return $n;
    }

    public static function buildFileKey(int $userId, ?array $folder, string $storedName, ?DateTimeInterface $date = null): string
    {
        if ($storedName === '' || preg_match('/[\x00-\x1F\x7F]/', $storedName) === 1) throw new InvalidArgumentException('Nombre interno inválido.');
        $date ??= new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $base = self::joinS3Prefix(self::normalizeRootPrefix($userId), 'uploads', $date->format('Y'), $date->format('m'));
        if (is_array($folder) && trim((string)($folder['prefix'] ?? '')) !== '') { $base = self::normalizeFolderPrefix($userId, (string)$folder['prefix']); }
        $key = $base . ltrim($storedName, '/');
        self::assertSafeScope($userId, $key);
        return $key;
    }


    public static function buildInboundAttachmentKey(int $userId, int $messageId, string $storedName, ?DateTimeInterface $date = null): string
    {
        if ($storedName === '' || preg_match('/[\x00-\x1F\x7F]/', $storedName) === 1) throw new InvalidArgumentException('Nombre interno inválido.');
        $date ??= new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $base = self::joinS3Prefix(self::normalizeRootPrefix($userId), 'mail', 'inbound', 'attachments', $date->format('Y'), $date->format('m'), (string) $messageId);
        $key = $base . ltrim($storedName, '/');
        self::assertSafeScope($userId, $key);
        return $key;
    }

    public static function keyScope(int $userId, string $key): string
    {
        $root = self::normalizeRootPrefix($userId);
        if (!str_starts_with($key, 'users/')) return 'root_bucket';
        if (!str_starts_with($key, $root)) return 'outside_user_root';
        if (str_starts_with($key, $root . $userId . '/')) return 'duplicated_user_segment';
        if (str_starts_with($key, $root . 'trash/')) return 'trash_ok';
        return 'ok';
    }

    public static function assertSafeScope(int $userId, string $key): void
    {
        if (str_contains($key, '..') || str_contains($key, '//') || preg_match('/[\x00-\x1F\x7F]/', $key) === 1) throw new InvalidArgumentException('s3_key inválida por política de seguridad.');
        $scope = self::keyScope($userId, $key);
        if ($scope !== 'ok' && $scope !== 'trash_ok') throw new InvalidArgumentException('s3_key fuera de alcance permitido: ' . $scope);
    }
}
