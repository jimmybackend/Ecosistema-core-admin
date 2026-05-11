<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class CloudStorageService
{
    public function __construct(private array $config, private bool $awsSdkAvailable)
    {
    }

    public function storeUploadedFile(array $file, string $internalName): array
    {
        $cloud = $this->config['cloud'] ?? [];
        $uploadsAllowed = (bool) ($cloud['allow_uploads'] ?? false);
        $s3Enabled = (bool) ($cloud['s3_enabled'] ?? false);

        if (!$uploadsAllowed) {
            return ['ok' => false, 'message' => 'Subidas deshabilitadas por configuración.'];
        }

        if ($s3Enabled) {
            if (!$this->awsSdkAvailable) {
                return ['ok' => false, 'message' => 'S3 habilitado pero AWS SDK no está instalado en este proyecto.'];
            }

            return ['ok' => false, 'message' => 'S3 real requiere implementación posterior controlada.'];
        }

        $basePath = trim((string) (($cloud['local_storage_path'] ?? 'storage/app/cloud')), '/');
        $targetDirectory = dirname(__DIR__, 3) . '/' . $basePath;
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
            return ['ok' => false, 'message' => 'No se pudo preparar almacenamiento local de Cloud.'];
        }

        $prefix = trim((string) ($cloud['upload_prefix'] ?? 'uploads'), '/');
        $targetPath = $targetDirectory . '/' . ($prefix !== '' ? $prefix . '/' : '') . $internalName;
        $targetFolder = dirname($targetPath);
        if (!is_dir($targetFolder) && !mkdir($targetFolder, 0750, true) && !is_dir($targetFolder)) {
            return ['ok' => false, 'message' => 'No se pudo preparar almacenamiento local de Cloud.'];
        }

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            return ['ok' => false, 'message' => 'No se pudo almacenar el archivo subido.'];
        }

        return ['ok' => true, 'disk' => 'local', 'key' => ($prefix !== '' ? $prefix . '/' : '') . $internalName, 'found_in_s3' => 0];
    }
}
