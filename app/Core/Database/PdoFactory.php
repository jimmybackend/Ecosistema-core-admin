<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;

final class PdoFactory
{
    /**
     * @param array<string, mixed> $databaseConfig
     */
    public static function make(array $databaseConfig): PDO
    {
        $connectionName = (string) ($databaseConfig['default'] ?? 'mysql');
        $connections = $databaseConfig['connections'] ?? [];
        $connection = $connections[$connectionName] ?? [];

        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (int) ($connection['port'] ?? 3306);
        $database = (string) ($connection['database'] ?? '');
        $charset = (string) ($connection['charset'] ?? 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

        return new PDO(
            $dsn,
            (string) ($connection['username'] ?? ''),
            (string) ($connection['password'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
