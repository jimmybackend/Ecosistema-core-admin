<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use InvalidArgumentException;

final readonly class CloudUploadService
{
    public function __construct(private CloudFileRepository $files, private CloudStorageService $storage, private array $config)
    {
    }

    public function options(): array
    {
        $cloud = $this->config['cloud'] ?? [];
        return [
            'allow_uploads' => (bool) ($cloud['allow_uploads'] ?? false),
            's3_enabled' => (bool) ($cloud['s3_enabled'] ?? false),
            'max_upload_mb' => (int) ($cloud['max_upload_mb'] ?? 10),
            'allowed_extensions' => (array) ($cloud['allowed_extensions'] ?? []),
        ];
    }

    public function upload(int $tenantId, int $userId, array $fileInput, ?array $folder = null): array
    {
        $opts = $this->options();
        if (!$opts['allow_uploads']) return ['ok' => false, 'message' => 'Subidas deshabilitadas por configuración.'];
        if (!isset($fileInput['tmp_name'], $fileInput['name'], $fileInput['error'], $fileInput['size'])) return ['ok' => false, 'message' => 'Archivo inválido o no enviado.'];
        if ((int) $fileInput['error'] !== UPLOAD_ERR_OK) return ['ok' => false, 'message' => 'No se pudo procesar el archivo subido.'];

        $maxBytes = max(1, (int) $opts['max_upload_mb']) * 1024 * 1024;
        if ((int) $fileInput['size'] > $maxBytes) return ['ok' => false, 'message' => 'El archivo excede el tamaño máximo permitido.'];

        $originalName = $this->sanitizeOriginalName((string) $fileInput['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = array_map('strtolower', (array) $opts['allowed_extensions']);
        $blockedAlways = ['php', 'phtml', 'phar', 'exe', 'sh', 'js', 'html'];
        if (in_array($extension, $blockedAlways, true) || !in_array($extension, $allowedExtensions, true)) return ['ok' => false, 'message' => 'La extensión del archivo no está permitida.'];

        $storedName = bin2hex(random_bytes(16)) . ($extension !== '' ? ('.' . $extension) : '');
        try { $s3Key = $this->buildSafeS3Key($userId, $folder, $storedName); } catch (InvalidArgumentException $e) { return ['ok' => false, 'message' => $e->getMessage()]; }
        $mimeType = (string) ($fileInput['type'] ?? 'application/octet-stream');
        $sourcePath = (string) ($fileInput['tmp_name'] ?? '');
        $checksum = $this->computeChecksum($sourcePath);

        $store = $this->storage->storeUploadedFile($fileInput, $s3Key);
        if (!($store['ok'] ?? false)) return ['ok' => false, 'message' => (string) ($store['message'] ?? 'No se pudo almacenar el archivo.')];

        $id = $this->files->createUploaded([
            'tenant_id' => $tenantId, 'user_id' => $userId, 'original_name' => $originalName,
            'stored_name' => $storedName, 's3_key' => (string) $store['key'], 'mime_type' => $mimeType,
            'extension' => $extension, 'size_bytes' => (int) $fileInput['size'], 'checksum_sha256' => is_string($checksum) ? $checksum : null,
            'origin_module' => 'cloud', 'access_type' => 'normal', 'found_in_s3' => (int) ($store['found_in_s3'] ?? 1), 'virus_scan_status' => 'pending', 'status' => 'active',
        ]);

        return $id > 0 ? ['ok' => true, 'id' => $id, 'message' => 'Archivo subido correctamente.'] : ['ok' => false, 'message' => 'No se pudo registrar metadata del archivo.'];
    }

    private function buildSafeS3Key(int $userId, ?array $folder, string $storedName): string
    {
        if ($storedName === '' || preg_match('/[\x00-\x1F\x7F]/', $storedName) === 1) throw new InvalidArgumentException('Nombre interno inválido.');
        $prefix = 'users/' . $userId . '/';
        $path = $prefix . 'uploads/' . gmdate('Y') . '/' . gmdate('m') . '/';
        if (is_array($folder) && trim((string)($folder['prefix'] ?? '')) !== '') $path = trim((string)$folder['prefix']);
        $normalized = trim(str_replace('\\', '/', $path));
        $normalized = preg_replace('#/+#', '/', $normalized) ?? '';
        $normalized = trim($normalized, '/') . '/';
        if ($normalized === '/' || str_starts_with($normalized, '/') || str_contains($normalized, '..') || preg_match('/[\x00-\x1F\x7F]/', $normalized) === 1) throw new InvalidArgumentException('Prefijo de carpeta inválido.');
        if (!str_starts_with($normalized, $prefix)) throw new InvalidArgumentException('Prefijo fuera de la raíz de usuario.');
        $key = $normalized . $storedName;
        if (str_contains($key, '//') || str_contains($key, '../') || !str_ends_with($key, '/' . $storedName)) throw new InvalidArgumentException('s3_key inválida por política de seguridad.');
        return $key;
    }

    private function sanitizeOriginalName(string $name): string { $base = basename(str_replace('\\', '/', $name)); $clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base); return $clean !== '' ? $clean : 'archivo'; }
    private function computeChecksum(string $path): ?string { if ($path === '' || !is_file($path) || !is_readable($path)) return null; $checksum = hash_file('sha256', $path); return is_string($checksum) && $checksum !== '' ? $checksum : null; }
}
