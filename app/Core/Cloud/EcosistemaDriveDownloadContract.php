<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveDownloadContract
{
    /**
     * @return array<int,string>
     */
    public function getRequiredChecks(): array
    {
        return [
            'Sesión autenticada.',
            'Permiso cloud.view.',
            'tenant_id del archivo debe coincidir con tenant_id autenticado.',
            'user_id debe coincidir cuando la política del archivo lo requiera.',
            'El archivo debe existir en cloud_files.',
            'El archivo no debe estar eliminado/inactivo.',
            'Si existe virus_scan_status, debe estar en estado seguro para descarga.',
            'No descargar si la configuración de Drive bloquea descargas.',
            'Registrar auditoría de intento de descarga (permitido o denegado).',
        ];
    }

    /** @return array<int,string> */
    public function getBlockedInputs(): array
    {
        return [
            's3_key (query/body/header)',
            'stored_name (query/body/header)',
            'Rutas internas de storage o filesystem (query/body/header)',
            'bucket real o key real desde parámetros del cliente',
            'metadata_json crudo y cualquier secreto operativo',
        ];
    }

    /** @return array<int,string> */
    public function getAllowedModes(): array
    {
        return [
            'contract',
            'dry-run',
            'read-only',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function describe(): array
    {
        return [
            'title' => 'Contrato de descarga Drive (futuro)',
            'notice' => 'No hay descarga real en este PR',
            'required_checks' => $this->getRequiredChecks(),
            'blocked_inputs' => $this->getBlockedInputs(),
            'allowed_modes' => $this->getAllowedModes(),
            'forbidden_operations' => [
                'Generar signed URLs reales.',
                'Conectar AWS/S3 real.',
                'Abrir archivos locales o remotos.',
                'Exponer s3_key, stored_name o rutas internas.',
                'Modificar base de datos.',
            ],
            'expected_audit' => [
                'drive.download.attempted',
                'drive.download.allowed (futuro)',
                'drive.download.denied (futuro)',
            ],
        ];
    }
}
