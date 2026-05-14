<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingFormSubmitDryRunService
{
    public function __construct(private EcosistemaLandingFormRepository $repository)
    {
    }

    public function buildForm(int $tenantId, int $formId, bool $enabled): array
    {
        if (!$enabled) { return ['enabled' => false, 'allowed' => false, 'errors' => ['feature_flag' => 'Dry-run deshabilitado por configuración.']]; }
        if ($formId <= 0) { return ['enabled' => true, 'allowed' => false, 'errors' => ['id' => 'ID inválido.']]; }
        $form = $this->repository->findForm($tenantId, $formId);
        if ($form === null) { return ['enabled' => true, 'allowed' => false, 'errors' => ['form' => 'Formulario no encontrado para el tenant actual.']]; }
        $fields = array_values(array_filter($this->repository->listFieldsForForm($tenantId, $formId), fn(array $f): bool => (int)($f['is_active'] ?? 0) === 1));

        return ['enabled' => true, 'allowed' => true, 'form' => ['id' => (int)$form['id'], 'name' => (string)($form['name'] ?? '')], 'fields' => array_map(fn(array $f): array => $this->toFieldRule($f), $fields)];
    }

    public function simulateSubmit(int $tenantId, int $formId, bool $enabled, array $payload): array
    {
        $base = $this->buildForm($tenantId, $formId, $enabled);
        if (($base['allowed'] ?? false) !== true) { return $base + ['input_preview' => [], 'would_store' => [], 'spam' => ['is_spam' => false, 'score' => 0, 'reasons' => []]]; }
        $errors = [];
        $inputPreview = [];
        $wouldStore = [];
        $spam = ['is_spam' => false, 'score' => 0, 'reasons' => []];

        foreach ((array)($base['fields'] ?? []) as $field) {
            $key = (string)($field['field_key'] ?? '');
            if ($key === '') { continue; }
            $value = isset($payload[$key]) ? trim((string)$payload[$key]) : '';
            $inputPreview[$key] = $this->maskValue($value, (string)$field['field_type']);
            if (($field['is_required'] ?? false) === true && $value === '') { $errors[$key] = 'Campo requerido.'; continue; }
            if ($value === '') { continue; }
            $typeError = $this->validateType($value, (string)$field['field_type']);
            if ($typeError !== null) { $errors[$key] = $typeError; continue; }
            if (mb_strlen($value) > (int)$field['max_length']) { $errors[$key] = 'Longitud excedida.'; continue; }
            $spamScore = $this->spamScore($value);
            $spam['score'] += $spamScore['score'];
            $spam['reasons'] = array_merge($spam['reasons'], $spamScore['reasons']);
            $wouldStore[$key] = ['field_id' => (int)$field['id'], 'field_label' => (string)$field['label'], 'value_preview' => $this->maskValue($value, (string)$field['field_type']), 'stored_full_value' => false];
        }

        $spam['is_spam'] = $spam['score'] >= 4;
        if ($spam['is_spam']) { $errors['_spam'] = 'Payload marcado como spam potencial.'; }

        return $base + ['input_preview' => $inputPreview, 'would_store' => $wouldStore, 'errors' => $errors, 'valid' => $errors === [], 'dry_run' => true, 'db_write' => false, 'crm_lead_write' => false, 'spam' => $spam];
    }

    private function toFieldRule(array $field): array
    {
        $validation = json_decode((string)($field['validation_json'] ?? ''), true);
        $maxLength = is_array($validation) && isset($validation['max_length']) ? max(1, (int)$validation['max_length']) : 255;
        return ['id' => (int)($field['id'] ?? 0), 'field_key' => (string)($field['field_key'] ?? ''), 'label' => (string)($field['label'] ?? ''), 'field_type' => (string)($field['field_type'] ?? 'text'), 'is_required' => (int)($field['is_required'] ?? 0) === 1, 'max_length' => min($maxLength, 2000)];
    }

    private function validateType(string $value, string $type): ?string
    {
        return match ($type) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Formato email inválido.',
            'number' => is_numeric($value) ? null : 'Debe ser numérico.',
            'tel' => preg_match('/^[0-9+()\-\s]{7,25}$/', $value) === 1 ? null : 'Formato teléfono inválido.',
            'url' => filter_var($value, FILTER_VALIDATE_URL) ? null : 'Formato URL inválido.',
            default => null,
        };
    }

    private function spamScore(string $value): array
    {
        $score = 0; $reasons = [];
        if (preg_match('/https?:\/\//i', $value) === 1) { $score += 2; $reasons[] = 'contains_url'; }
        if (preg_match('/(free money|viagra|casino|crypto)/i', $value) === 1) { $score += 3; $reasons[] = 'keyword'; }
        if (preg_match('/(.)\1{6,}/u', $value) === 1) { $score += 2; $reasons[] = 'repeated_chars'; }
        return ['score' => $score, 'reasons' => $reasons];
    }

    private function maskValue(string $value, string $type): ?string
    {
        if ($value === '') { return null; }
        if ($type === 'email') { return preg_replace('/(^.).*(@.*$)/', '$1***$2', $value) ?: '***'; }
        if ($type === 'tel') { return '***' . mb_substr($value, -2); }
        return mb_strlen($value) > 60 ? mb_substr($value, 0, 60) . '…' : $value;
    }
}
