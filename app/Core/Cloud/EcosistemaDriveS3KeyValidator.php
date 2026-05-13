<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveS3KeyValidator
{
    /**
     * @return array{key_shape_status:string,warnings:array<int,string>,blocked_reasons:array<int,string>}
     */
    public function validateShape(?string $s3Key): array
    {
        $warnings = [];
        $blockedReasons = [];

        $key = is_string($s3Key) ? trim($s3Key) : '';
        if ($key === '') {
            return [
                'key_shape_status' => 'missing',
                'warnings' => ['No hay s3_key registrada para validación futura.'],
                'blocked_reasons' => ['No se puede validar formato S3 sin key registrada.'],
            ];
        }

        if (strlen($key) > 1024) {
            $blockedReasons[] = 'La key excede 1024 caracteres permitidos por política interna.';
        }

        $lower = strtolower($key);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://') || str_starts_with($lower, 's3://')) {
            $blockedReasons[] = 'La key no puede ser una URL absoluta ni URI s3://.';
        }

        if (str_starts_with($key, '/')) {
            $blockedReasons[] = 'La key no puede iniciar con /.'.'';
        }

        if (str_contains($key, '..')) {
            $blockedReasons[] = 'La key no puede contener secuencias de traversal (..).';
        }

        if (str_contains($key, '\\')) {
            $blockedReasons[] = 'La key no puede contener backslash.';
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $key) === 1) {
            $blockedReasons[] = 'La key no puede contener caracteres de control.';
        }

        return [
            'key_shape_status' => $blockedReasons === [] ? 'valid' : 'invalid',
            'warnings' => $warnings,
            'blocked_reasons' => $blockedReasons,
        ];
    }
}
