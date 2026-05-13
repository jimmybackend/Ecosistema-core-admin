<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveAdapter
{
    public function __construct(private readonly EcosistemaDriveConfig $config)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->config->isEnabled(),
            'mode' => $this->config->mode(),
            'reference_repo' => $this->config->referenceRepo(),
            'api_timeout' => $this->config->apiTimeout(),
            'remote_calls_blocked' => !$this->config->allowsRemoteCalls(),
            'signed_urls_blocked' => !$this->config->allowsSignedUrls(),
            'remote_uploads_blocked' => !$this->config->allowsRemoteUploads(),
            'remote_downloads_blocked' => !$this->config->allowsRemoteDownloads(),
            'database_required' => false,
            'external_http_allowed' => false,
            'aws_s3_config_prepared' => true,
            'aws_connection' => false,
            'aws_connected' => false,
            'signed_urls' => false,
            'signed_url_dry_run' => true,
            'remote_downloads' => false,
            'remote_uploads' => false,
            'remote_calls' => false,
            'storage_reads' => false,
            'storage_writes' => false,
            'contract_only' => true,
            'controlled_download' => true,
            'controlled_download_available' => false,
            'db_writes' => false,
            'share_links' => false,
            'public_links' => false,
            'share_tokens' => false,
            'email_notifications' => false,
            'upload_dry_run' => true,
            'read_file_versions' => true,
            'version_restore' => false,
            'version_download' => false,
        ];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function getCapabilities(): array
    {
        return [
            'configuration_contract' => [
                'enabled' => true,
                'description' => 'Expone estado seguro de configuración para integración futura de Ecosistema Drive.',
            ],
            'remote_calls' => [
                'enabled' => false,
                'description' => 'Bloqueadas en modo contract/dry-run.',
            ],
            'signed_url_dry_run' => [
                'enabled' => true,
                'description' => 'Simulación segura de elegibilidad para signed URL futura, sin generar URL real ni conectar AWS/S3.',
            ],
            'upload_dry_run' => [
                'enabled' => true,
                'description' => 'Simulación informativa de subida S3 en modo dry-run, sin procesar archivos ni escribir en DB/storage.',
            ],
            'read_file_versions' => [
                'enabled' => true,
                'description' => 'Lectura read-only de metadata de versiones por archivo desde cloud_file_versions, sin exponer s3_key/s3_version_id ni habilitar restore/download.',
            ],
            'version_restore' => [
                'enabled' => false,
                'description' => 'Restauración de versiones deshabilitada en este PR.',
            ],
            'version_download' => [
                'enabled' => false,
                'description' => 'Descarga de versiones deshabilitada en este PR.',
            ],
            'signed_urls' => [
                'enabled' => false,
                'description' => 'Generación de URLs firmadas deshabilitada en este PR.',
            ],
            'remote_uploads' => [
                'enabled' => false,
                'description' => 'Subidas remotas a S3/AWS no permitidas.',
            ],
            'remote_downloads' => [
                'enabled' => false,
                'description' => 'Descargas remotas desde S3/AWS no permitidas.',
            ],
            'controlled_download' => [
                'enabled' => true,
                'description' => 'Flujo de descarga controlada por backend con bloqueo por defecto y múltiples banderas de seguridad.',
            ],
            'read_user_root' => [
                'enabled' => true,
                'description' => 'Lectura read-only de raíz de usuario desde cloud_user_roots (sin exponer root_prefix/rutas internas).',
            ],
            'read_buckets_metadata' => [
                'enabled' => true,
                'description' => 'Lectura read-only de metadata de buckets desde cloud_buckets (sin AWS/S3 real).',
            ],
            'read_metadata' => [
                'enabled' => true,
                'description' => 'Lectura read-only de metadata desde cloud_files (sin llamadas AWS/S3).',
            ],
            'read_folders_metadata' => [
                'enabled' => true,
                'description' => 'Lectura read-only de metadata de carpetas desde cloud_folders (sin llamadas AWS/S3).',
            ],
            'read_folder_detail' => [
                'enabled' => true,
                'description' => 'Detalle read-only de carpeta por id usando cloud_folders (sin exponer prefix/rutas internas).',
            ],
            'read_folder_navigation' => [
                'enabled' => true,
                'description' => 'Navegación básica read-only por carpetas/archivos con metadata DB (sin AWS/S3 real).',
            ],
            'read_drive_summary' => [
                'enabled' => true,
                'description' => 'Resumen operativo read-only de Drive con conteos seguros y estado general (sin AWS/S3 real).',
            ],
            'read_access_policy' => [
                'enabled' => true,
                'description' => 'Política interna read-only de acceso Drive para tenant/user y operaciones bloqueadas.',
            ],
            'read_only_audit' => [
                'enabled' => true,
                'description' => 'Auditoría read-only de visualización administrativa de Drive en core_audit, sin exponer keys/prefixes/secretos.',
            ],
            'safe_s3_key_validation' => [
                'enabled' => true,
                'description' => 'Validación dry-run segura de s3_key sin exponer la key ni habilitar descarga real.',
            ],
            'share_contract' => [
                'enabled' => true,
                'description' => 'Contrato técnico/documental read-only para compartir archivos en el futuro, sin crear links/tokens/shares.',
            ],
            'download_contract' => [
                'enabled' => true,
                'description' => 'Contrato técnico/documental para futura descarga controlada de Drive, sin descarga real ni AWS/S3.',
            ],
            's3_reference_only' => [
                'enabled' => true,
                'description' => 'El repositorio s3 se usa solo como referencia técnica/funcional.',
            ],
        ];
    }
}
