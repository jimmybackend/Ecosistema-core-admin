<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

use PDO;

final readonly class EcosistemaSendNotificationDryRunService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function simulate(int $tenantId, array $input): array
    {
        $templateId = (int) ($input['template_id'] ?? 0);
        if ($templateId <= 0) {
            return $this->error('template_id inválido.');
        }

        $template = $this->findTemplate($tenantId, $templateId);
        if ($template === null) {
            return $this->error('Template no encontrado para el tenant actual.');
        }

        $recipient = $this->validateRecipient($tenantId, $input);
        if (!$recipient['ok']) {
            return $this->error((string) ($recipient['error'] ?? 'Destinatario inválido.'));
        }

        $payload = $this->validatePayloadJson((string) ($input['payload_json'] ?? ''));
        if (!$payload['ok']) {
            return $this->error((string) ($payload['error'] ?? 'payload_json inválido.'));
        }

        $subject = $this->renderTemplate((string) ($template['subject'] ?? ''), (array) $payload['variables']);
        $body = $this->renderTemplate((string) ($template['body'] ?? ''), (array) $payload['variables']);

        return [
            'ok' => true,
            'mode' => 'dry-run',
            'would_queue' => true,
            'would_send' => true,
            'send_executed' => false,
            'queue_created' => false,
            'smtp_connection' => false,
            'template_id' => (int) $template['id'],
            'channel_id' => isset($template['channel_id']) ? (int) $template['channel_id'] : null,
            'channel_code' => (string) ($template['channel_code'] ?? ''),
            'recipient' => $recipient,
            'subject_preview' => $this->sanitizePreview($subject, 180),
            'body_preview' => $this->sanitizePreview($body, 3000),
            'payload_safe' => (array) $payload['variables'],
            'warnings' => (array) $payload['warnings'],
        ];
    }

    private function findTemplate(int $tenantId, int $templateId): ?array
    {
        $sql = 'SELECT nt.id,nt.channel_id,nt.subject,nt.body,nt.variables_json,nc.code AS channel_code,nc.name AS channel_name FROM notifications_templates nt INNER JOIN notifications_channels nc ON nc.id=nt.channel_id AND nc.tenant_id=nt.tenant_id WHERE nt.tenant_id=:tenant_id AND nt.id=:id AND nt.is_active=1 LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':id' => $templateId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function validateRecipient(int $tenantId, array $input): array
    {
        $userId = (int) ($input['recipient_user_id'] ?? 0);
        $emailPreview = trim((string) ($input['recipient_email_preview'] ?? ''));

        if ($userId > 0) {
            $stmt = $this->pdo->prepare('SELECT id,email,status FROM core_users WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
            $stmt->execute([':tenant_id' => $tenantId, ':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($user)) {
                return ['ok' => false, 'error' => 'recipient_user_id no pertenece al tenant actual.'];
            }

            return ['ok' => true, 'type' => 'user', 'user_id' => (int) $user['id'], 'email_preview' => $this->maskEmail((string) ($user['email'] ?? ''))];
        }

        if ($emailPreview === '' || filter_var($emailPreview, FILTER_VALIDATE_EMAIL) === false) {
            return ['ok' => false, 'error' => 'Debe enviar recipient_user_id válido o recipient_email_preview válido.'];
        }

        return ['ok' => true, 'type' => 'email_preview', 'user_id' => null, 'email_preview' => $this->maskEmail($emailPreview)];
    }

    private function validatePayloadJson(string $payloadJson): array
    {
        $json = trim($payloadJson);
        if ($json === '') {
            return ['ok' => true, 'variables' => [], 'warnings' => []];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'payload_json debe ser JSON objeto simple.'];
        }

        $variables = [];
        $warnings = [];
        foreach ($decoded as $key => $value) {
            $name = trim((string) $key);
            if ($name === '' || preg_match('/^[a-zA-Z0-9_.-]{1,64}$/', $name) !== 1) {
                $warnings[] = 'variable_name_rejected:' . $name;
                continue;
            }
            if (!is_scalar($value) && $value !== null) {
                $warnings[] = 'variable_value_rejected:' . $name;
                continue;
            }
            $variables[$name] = $this->sanitizePreview((string) $value, 300);
        }

        return ['ok' => true, 'variables' => $variables, 'warnings' => array_values(array_unique($warnings))];
    }

    private function renderTemplate(string $template, array $variables): string
    {
        $result = $template;
        foreach ($variables as $name => $value) {
            $result = str_replace('{{' . $name . '}}', (string) $value, $result);
            $result = str_replace('{{ ' . $name . ' }}', (string) $value, $result);
        }

        return $result;
    }

    private function error(string $message): array
    {
        return ['ok' => false, 'mode' => 'dry-run', 'would_queue' => false, 'would_send' => false, 'send_executed' => false, 'queue_created' => false, 'smtp_connection' => false, 'error' => $message];
    }

    private function sanitizePreview(string $value, int $maxLength): string
    {
        $clean = trim(strip_tags($value));
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $clean) ?? '';
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';
        $head = mb_substr($clean, 0, $maxLength);

        return $head === $clean ? $head : ($head . '…');
    }

    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $this->sanitizePreview($email, 12);
        }

        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1) . '***@' . $domain;
    }
}
