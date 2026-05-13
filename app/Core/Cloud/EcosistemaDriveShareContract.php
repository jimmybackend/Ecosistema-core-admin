<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveShareContract
{
    /**
     * @return array<string,mixed>
     */
    public function describe(): array
    {
        return [
            'mode' => 'contract/read-only',
            'share_enabled' => false,
            'public_links_enabled' => false,
            'token_generated' => false,
            'email_notifications_enabled' => false,
            'aws_connection' => false,
            'download_enabled' => false,
            'required_checks' => [
                'authenticated_session',
                'cloud.view permission',
                'positive integer file_id',
                'tenant isolation',
                'user isolation',
                'file status not deleted',
            ],
            'blocked_operations' => [
                'create_share_record',
                'create_public_link',
                'generate_access_token',
                'send_email_notifications',
                'connect_aws_s3',
                'storage_write',
                'database_write',
            ],
            'allowed_future_share_modes' => [
                'internal_user',
                'internal_role',
                'tenant_link',
                'time_limited_public_link',
            ],
            'forbidden_inputs' => [
                'email',
                'user_id',
                'role_id',
                'token',
                'expires_at',
                'permission',
            ],
            'audit_expectations' => [
                'action' => 'drive.file.share_contract.viewed',
                'read_only' => true,
                'token_generated' => false,
                'share_created' => false,
                'public_link_created' => false,
                'email_sent' => false,
                'secrets_exposed' => false,
            ],
            'safe_response_shape' => [
                'file' => ['id', 'original_name', 'mime_type', 'size_bytes', 'status'],
                'contract' => 'metadata-only',
                'sensitive_fields_excluded' => ['s3_key', 's3_version_id', 'stored_name', 'prefixes', 'config_json', 'tokens', 'signed_urls'],
            ],
        ];
    }
}
