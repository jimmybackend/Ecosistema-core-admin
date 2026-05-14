<?php

declare(strict_types=1);

namespace App\Core\MailNotifications;

final readonly class EcosistemaMessagePreviewDryRunService
{
    public function __construct(
        private EcosistemaNotificationTemplateRepository $notificationRepository,
        private EcosistemaUrlMessageTemplateRepository $urlMessageRepository
    ) {
    }

    public function previewNotificationTemplate(int $tenantId, int $id, array $requestVariables = []): ?array
    {
        $template = $this->notificationRepository->findTemplate($tenantId, $id);
        if ($template === null) {
            return null;
        }

        $subject = (string) ($template['subject'] ?? '');
        $body = (string) ($template['body'] ?? '');
        $allowed = $this->extractAllowedVariables((string) ($template['variables_json'] ?? ''), $subject . ' ' . $body);
        [$safeVariables, $warnings] = $this->sanitizeAllowedVariables($requestVariables, $allowed);

        return $this->toDryRunDto($subject, $body, $safeVariables, $warnings);
    }

    public function previewUrlMessageTemplate(int $tenantId, int $id, array $requestVariables = []): ?array
    {
        $template = $this->urlMessageRepository->findTemplate($tenantId, $id);
        if ($template === null) {
            return null;
        }

        $subject = (string) ($template['subject'] ?? '');
        $body = trim(implode("\n", array_filter([
            (string) ($template['header_html'] ?? ''),
            (string) ($template['body_html'] ?? ''),
            (string) ($template['footer_html'] ?? ''),
            (string) ($template['plain_text'] ?? ''),
        ], static fn(string $value): bool => trim($value) !== '')));

        $allowed = $this->extractAllowedVariables('', $subject . ' ' . $body);
        [$safeVariables, $warnings] = $this->sanitizeAllowedVariables($requestVariables, $allowed);

        return $this->toDryRunDto($subject, $body, $safeVariables, $warnings);
    }

    private function toDryRunDto(string $subject, string $body, array $variables, array $warnings): array
    {
        return [
            'mode' => 'dry-run',
            'preview_generated' => true,
            'send_executed' => false,
            'queue_created' => false,
            'smtp_connection' => false,
            'subject_preview' => $this->sanitizePreview($this->applyVariables($subject, $variables), 180),
            'body_preview' => $this->sanitizePreview($this->applyVariables($body, $variables), 3000),
            'variables_used' => $variables,
            'warnings' => $warnings,
        ];
    }

    private function extractAllowedVariables(string $variablesJson, string $content): array
    {
        $allowed = [];
        $decoded = json_decode($variablesJson, true);
        if (is_array($decoded)) {
            foreach ($decoded as $key => $value) {
                $varName = is_string($key) ? trim($key) : trim((string) $value);
                if (preg_match('/^[a-zA-Z0-9_.-]{1,64}$/', $varName) === 1) {
                    $allowed[$varName] = true;
                }
            }
        }

        if (preg_match_all('/\{\{\s*([a-zA-Z0-9_.-]{1,64})\s*\}\}/', $content, $matches) === 1 || !empty($matches[1])) {
            foreach ($matches[1] as $name) {
                $allowed[(string) $name] = true;
            }
        }

        return array_keys($allowed);
    }

    private function sanitizeAllowedVariables(array $requestVariables, array $allowed): array
    {
        $allowedMap = array_fill_keys($allowed, true);
        $safe = [];
        $warnings = [];

        foreach ($requestVariables as $key => $value) {
            $name = trim((string) $key);
            if ($name === '' || !isset($allowedMap[$name])) {
                $warnings[] = 'variable_not_allowed:' . $name;
                continue;
            }

            $scalar = is_scalar($value) ? (string) $value : '';
            $safe[$name] = $this->sanitizePreview($scalar, 300);
        }

        return [$safe, array_values(array_unique($warnings))];
    }

    private function applyVariables(string $template, array $variables): string
    {
        if ($variables === []) {
            return $template;
        }

        $result = $template;
        foreach ($variables as $name => $value) {
            $result = str_replace('{{' . $name . '}}', (string) $value, $result);
            $result = str_replace('{{ ' . $name . ' }}', (string) $value, $result);
        }

        return $result;
    }

    private function sanitizePreview(string $value, int $maxLength): string
    {
        $clean = trim(strip_tags($value));
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $clean) ?? '';
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';
        $head = mb_substr($clean, 0, $maxLength);

        return $head === $clean ? $head : ($head . '…');
    }
}
