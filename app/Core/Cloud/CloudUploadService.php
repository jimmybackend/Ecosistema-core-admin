<?php

declare(strict_types=1);

namespace App\Core\Cloud;

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

    public function upload(int $tenantId, int $userId, array $fileInput): array
    {
        $opts = $this->options();
        if (!$opts['allow_uploads']) {
            return ['ok' => false, 'message' => 'Subidas deshabilitadas por configuración.'];
        }
        if (!isset($fileInput['tmp_name'], $fileInput['name'], $fileInput['error'], $fileInput['size'])) {
            return ['ok' => false, 'message' => 'Archivo inválido o no enviado.'];
        }
        if ((int) $fileInput['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'No se pudo procesar el archivo subido.'];
        }

        $maxBytes = max(1, (int) $opts['max_upload_mb']) * 1024 * 1024;
        if ((int) $fileInput['size'] > $maxBytes) {
            return ['ok' => false, 'message' => 'El archivo excede el tamaño máximo permitido.'];
        }

        $originalName = $this->sanitizeOriginalName((string) $fileInput['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = array_map('strtolower', (array) $opts['allowed_extensions']);
        $blockedAlways = ['php', 'phtml', 'phar', 'exe', 'sh', 'js', 'html'];
        if (in_array($extension, $blockedAlways, true) || !in_array($extension, $allowedExtensions, true)) {
            return ['ok' => false, 'message' => 'La extensión del archivo no está permitida.'];
        }

        $internalName = 'users/' . $userId . '/' . bin2hex(random_bytes(16)) . ($extension !== '' ? ('.' . $extension) : '');
        $mimeType = (string) ($fileInput['type'] ?? 'application/octet-stream');
        $sourcePath = (string) ($fileInput['tmp_name'] ?? '');
        $checksum = $this->computeChecksum($sourcePath);

        $store = $this->storage->storeUploadedFile($fileInput, $internalName);
        if (!($store['ok'] ?? false)) {
            return ['ok' => false, 'message' => (string) ($store['message'] ?? 'No se pudo almacenar el archivo.')];
        }

        $id = $this->files->createUploaded([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'original_name' => $originalName,
            'stored_name' => $internalName,
            's3_key' => (string) $store['key'],
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size_bytes' => (int) $fileInput['size'],
            'checksum_sha256' => is_string($checksum) ? $checksum : null,
            'origin_module' => 'cloud',
            'access_type' => 'normal',
            'found_in_s3' => (int) ($store['found_in_s3'] ?? 0),
            'virus_scan_status' => 'pending',
            'status' => 'active',
        ]);

        return $id > 0 ? ['ok' => true, 'id' => $id, 'message' => 'Archivo subido correctamente.'] : ['ok' => false, 'message' => 'No se pudo registrar metadata del archivo.'];
    }

    private function sanitizeOriginalName(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));
        $clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
        return $clean !== '' ? $clean : 'archivo';
    }

    private function computeChecksum(string $path): ?string
    {
        if ($path === '' || !is_file($path) || !is_readable($path)) {
            error_log('[cloud-upload] checksum_sha256 omitted: source file missing or unreadable.');
            return null;
        }

        $checksum = hash_file('sha256', $path);
        if (!is_string($checksum) || $checksum === '') {
            error_log('[cloud-upload] checksum_sha256 omitted: hash calculation failed.');
            return null;
        }

        return $checksum;
    }
}
