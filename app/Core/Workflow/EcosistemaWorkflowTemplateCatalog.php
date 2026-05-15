<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final class EcosistemaWorkflowTemplateCatalog
{
    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        return [
            'crm_lead_welcome' => [
                'key' => 'crm_lead_welcome','name' => 'CRM · Bienvenida de lead','description' => 'Cuando entra un lead, sugiere enviar correo y crear tarea de seguimiento.','trigger_module' => 'crm','trigger_event' => 'lead.created','actions' => ['send_email','create_task'],'status' => 'suggested','mode' => 'read-only',
            ],
            'landing_submission_triage' => [
                'key' => 'landing_submission_triage','name' => 'Landing · Triage de formulario','description' => 'Cuando llega formulario, sugiere ticket interno y notificación.','trigger_module' => 'landing','trigger_event' => 'form.submitted','actions' => ['create_ticket','create_notification'],'status' => 'suggested','mode' => 'read-only',
            ],
            'campaign_hot_lead' => [
                'key' => 'campaign_hot_lead','name' => 'Campaign · Lead caliente','description' => 'Cuando un lead cumple regla comercial, sugiere agenda y tarea.','trigger_module' => 'crm','trigger_event' => 'lead.scored_high','actions' => ['create_agenda_event','create_task'],'status' => 'suggested','mode' => 'read-only',
            ],
            'incident_escalation' => [
                'key' => 'incident_escalation','name' => 'Soporte · Escalamiento de incidente','description' => 'Cuando un caso crítico cambia de estado, sugiere webhook y notificación.','trigger_module' => 'support','trigger_event' => 'ticket.escalated','actions' => ['webhook','create_notification'],'status' => 'suggested','mode' => 'read-only',
            ],
        ];
    }
}
