<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingFormSubmitService
{
    public function __construct(private EcosistemaLandingFormRepository $formRepository, private EcosistemaLandingFormSubmitRepository $submitRepository)
    {
    }

    public function submit(int $tenantId, string $slug, int $formId, bool $enabled, bool $uploadsEnabled, array $payload, array $context): array
    {
        if (!$enabled) { return ['ok' => false, 'message' => 'Envío deshabilitado por configuración.']; }
        if ($formId <= 0 || $slug === '') { return ['ok' => false, 'message' => 'Solicitud inválida.']; }
        $form = $this->formRepository->findForm($tenantId, $formId);
        if ($form === null || (string)($form['landing_page_title'] ?? '') === '') { return ['ok' => false, 'message' => 'Formulario no disponible.']; }
        $fields = array_values(array_filter($this->formRepository->listFieldsForForm($tenantId, $formId), fn(array $f): bool => (int)($f['is_active'] ?? 0) === 1));
        $values = [];
        $errors = [];
        $raw = [];
        foreach ($fields as $field) {
            $key = trim((string)($field['field_key'] ?? ''));
            if ($key === '') { continue; }
            $v = trim((string)($payload[$key] ?? ''));
            $raw[$key] = $v;
            if ((int)($field['is_required'] ?? 0) === 1 && $v === '') { $errors[$key] = 'Campo requerido.'; continue; }
            if ($v === '') { continue; }
            $values[] = ['field_id' => (int)$field['id'], 'field_key' => $key, 'field_label' => (string)($field['label'] ?? ''), 'value_text' => mb_substr($v,0,2000), 'value_json' => '', 'file_path' => '', 's3_key' => ''];
        }
        if ($errors !== []) { return ['ok' => false, 'message' => 'Validación fallida.', 'errors' => $errors]; }
        if (!$uploadsEnabled) {
            // file uploads blocked in this controlled PR
        }
        $row = ['form_id'=>(int)$form['id'],'landing_page_id'=>(int)$form['landing_page_id'],'campaign_id'=>(int)($form['campaign_id']??0),'visit_id'=>0,'submitted_by_user_id'=>0,'contact_name'=>(string)($raw['name']??''),'email'=>(string)($raw['email']??''),'phone'=>(string)($raw['phone']??''),'company_name'=>(string)($raw['company_name']??''),'interest'=>(string)($raw['interest']??''),'message'=>(string)($raw['message']??''),'raw_data_json'=>json_encode($raw, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?: '{}','ip_address'=>(string)($context['ip_address']??''),'user_agent'=>(string)($context['user_agent']??''),'country'=>'','region'=>'','city'=>'','latitude'=>null,'longitude'=>null,'status'=>'received','spam_score'=>0];
        $submissionId = $this->submitRepository->createSubmission($tenantId, $row);
        foreach ($values as $value) { $this->submitRepository->insertSubmissionValue($tenantId, $submissionId, $value); }
        return ['ok' => true, 'message' => trim((string)($form['success_message'] ?? 'Envío recibido.')), 'submission_id' => $submissionId, 'crm_lead_write' => false];
    }
}
