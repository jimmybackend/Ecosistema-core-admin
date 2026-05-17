<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingFormService
{
    public function __construct(private EcosistemaLandingFormRepository $repository, private EcosistemaLandingAdapter $adapter)
    {
    }

    public function listForms(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeForms($tenantId),
            'forms' => array_map(fn(array $row): array => $this->toFormDto($row), $this->repository->listForms($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function listFormsForPage(int $tenantId, int $pageId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeForms($tenantId),
            'forms' => array_map(fn(array $row): array => $this->toFormDto($row), $this->repository->listFormsForPage($tenantId, $pageId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getFormDetail(int $tenantId, int $formId): ?array
    {
        $form = $this->repository->findForm($tenantId, $formId);
        if ($form === null) { return null; }

        return [
            'form' => $this->toFormDto($form),
            'fields' => array_map(fn(array $row): array => $this->toFieldDto($row), $this->repository->listFieldsForForm($tenantId, $formId)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function toFormDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0), 'landing_page_id' => isset($row['landing_page_id']) ? (int) $row['landing_page_id'] : null,
            'landing_page_title' => (string) ($row['landing_page_title'] ?? ''), 'campaign_id' => isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            'campaign_name' => (string) ($row['campaign_name'] ?? ''), 'name' => (string) ($row['name'] ?? ''),
            'description_preview' => $this->preview((string) ($row['description'] ?? ''), 140), 'submit_button_text' => (string) ($row['submit_button_text'] ?? ''),
            'success_message_present' => trim((string) ($row['success_message'] ?? '')) !== '', 'success_message_preview' => $this->preview((string) ($row['success_message'] ?? ''), 80),
            'redirect_url_present' => trim((string) ($row['redirect_url'] ?? '')) !== '', 'redirect_url_preview' => $this->preview((string) ($row['redirect_url'] ?? ''), 32),
            'redirect_url_exposed' => false, 'creates_crm_lead' => (bool) ($row['creates_crm_lead'] ?? false),
            'default_lead_source_id' => isset($row['default_lead_source_id']) ? (int) $row['default_lead_source_id'] : null,
            'default_funnel_stage_id' => isset($row['default_funnel_stage_id']) ? (int) $row['default_funnel_stage_id'] : null,
            'default_assigned_user_id' => isset($row['default_assigned_user_id']) ? (int) $row['default_assigned_user_id'] : null,
            'score_on_submit' => isset($row['score_on_submit']) ? (int) $row['score_on_submit'] : null,
            'is_active' => (bool) ($row['is_active'] ?? false), 'fields_count' => (int) ($row['fields_count'] ?? 0),
            'created_at' => $row['created_at'] ?? null, 'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only', 'db_write' => false, 'public_submit' => false, 'crm_lead_write' => false,
        ];
    }

    private function toFieldDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0), 'form_id' => (int) ($row['form_id'] ?? 0), 'field_key_present' => trim((string) ($row['field_key'] ?? '')) !== '',
            'field_key_preview' => $this->maskFieldKey((string) ($row['field_key'] ?? '')),
            'field_key_exposed' => false,
            'label' => (string) ($row['label'] ?? ''), 'field_type' => (string) ($row['field_type'] ?? ''),
            'placeholder_preview' => $this->preview((string) ($row['placeholder'] ?? ''), 64),
            'default_value_present' => trim((string) ($row['default_value'] ?? '')) !== '', 'default_value_exposed' => false,
            'options_json_present' => trim((string) ($row['options_json'] ?? '')) !== '', 'options_json_exposed' => false,
            'validation_json_present' => trim((string) ($row['validation_json'] ?? '')) !== '', 'validation_json_exposed' => false,
            'crm_target_table' => (string) ($row['crm_target_table'] ?? ''), 'crm_target_field' => (string) ($row['crm_target_field'] ?? ''),
            'is_required' => (bool) ($row['is_required'] ?? false), 'is_active' => (bool) ($row['is_active'] ?? false),
            'sort_order' => (int) ($row['sort_order'] ?? 0), 'created_at' => $row['created_at'] ?? null, 'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trim = trim($value);
        if ($trim === '') { return null; }
        $head = mb_substr($trim, 0, $max);

        return $head === $trim ? $head : $head . '…';
    }

    private function maskFieldKey(string $value): ?string
    {
        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        $visible = mb_substr($trim, 0, 2);
        return $visible . '***';
    }
}
