<?php

declare(strict_types=1);

namespace App\Core\Ai;

final readonly class EcosistemaAiProvider
{
    public function __construct(private array $config = []) {}

    public function assist(array $sanitizedContext): array
    {
        $providerEnabled = (bool) ($this->config['provider_enabled'] ?? false);
        if (!$providerEnabled) {
            return ['called' => false, 'provider' => 'none', 'output' => null, 'blocked_reason' => 'provider_disabled_by_flag'];
        }

        $summary = (string) ($sanitizedContext['summary_preview'] ?? '');
        return [
            'called' => true,
            'provider' => 'controlled_stub',
            'output' => [
                'proposal_type' => 'assist_summary',
                'summary' => $summary !== '' ? $summary : 'Sin contexto suficiente para sugerencia.',
                'rationale' => 'Salida controlada y sanitizada; sin incluir PII cruda.',
                'risk_level' => 'low',
                'benefit_level' => 'medium',
                'requires_human_confirmation' => 1,
            ],
            'blocked_reason' => null,
        ];
    }
}
