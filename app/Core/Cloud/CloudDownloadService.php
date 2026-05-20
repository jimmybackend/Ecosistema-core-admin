<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class CloudDownloadService
{
    public function __construct(private CloudFileRepository $files, private array $config)
    {
    }

    public function resolveLocalFile(int $tenantId, int $userId, int $id): array
    {
        $cloud = $this->config['cloud'] ?? [];
        if (!((bool) ($cloud['allow_downloads'] ?? false))) {
            return ['ok' => false, 'code' => 403, 'message' => 'Descargas deshabilitadas por configuración.'];
        }

        $file = $this->files->findDownloadableByIdForUser($tenantId, $userId, $id);
        if ($file === null) {
            return ['ok' => false, 'code' => 404, 'message' => 'Archivo no encontrado.'];
        }

        $status = (string) ($file['status'] ?? '');
        if ($status !== 'active') {
            return ['ok' => false, 'code' => 403, 'message' => 'El archivo no está disponible para descarga.'];
        }

        if (!((bool) ($cloud['s3_enabled'] ?? false))) {
            return ['ok' => false, 'code' => 403, 'message' => 'S3 deshabilitado por configuración.'];
        }

        $basePath = $this->buildBasePath((string) ($cloud['local_storage_path'] ?? 'storage/app/cloud'));
        $baseReal = realpath($basePath);
        if ($baseReal === false || !is_dir($baseReal)) {
            return ['ok' => false, 'code' => 404, 'message' => 'Archivo no encontrado.'];
        }

        $key = ltrim((string) ($file['s3_key'] ?? ''), '/');
        $target = $baseReal . '/' . $key;
        $targetReal = realpath($target);

        if ($targetReal === false || !is_file($targetReal)) {
            return ['ok' => false, 'code' => 404, 'message' => 'Archivo no encontrado.'];
        }

        $basePrefix = rtrim($baseReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($targetReal, $basePrefix)) {
            return ['ok' => false, 'code' => 403, 'message' => 'Ruta de archivo inválida.'];
        }

        $safeName = $this->sanitizeFileName((string) ($file['original_name'] ?? 'archivo'));
        $mime = trim((string) ($file['mime_type'] ?? ''));
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }

        return [
            'ok' => true,
            'path' => $targetReal,
            'file' => [
                'id' => (int) $file['id'],
                'original_name' => $safeName,
                'mime_type' => $mime,
                'status' => $status,
                'found_in_s3' => (int) ($file['found_in_s3'] ?? 0),
                's3_key' => (string) ($file['s3_key'] ?? ''),
            ],
        ];
    }

    private function buildBasePath(string $relativePath): string
    {
        return dirname(__DIR__, 3) . '/' . trim($relativePath, '/');
    }

    private function sanitizeFileName(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));
        $clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
        return $clean !== '' ? $clean : 'archivo';
    }
}
