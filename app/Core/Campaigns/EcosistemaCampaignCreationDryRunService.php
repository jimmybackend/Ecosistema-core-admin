<?php

declare(strict_types=1);

namespace App\Core\Campaigns;

final class EcosistemaCampaignCreationDryRunService
{
    public function __construct(private readonly array $config = []) {}

    public function simulate(int $tenantId, int $ownerUserId, array $input): array
    {
        $enabled = (bool) ($this->config['campaign_creation_dry_run'] ?? false);
        $result = [
            'mode' => 'dry-run',
            'enabled' => $enabled,
            'tenant_from_session' => true,
            'db_write' => false,
            'would_create_campaign' => false,
            'would_create_landing_page' => false,
            'would_create_short_link' => false,
            'blocked_reasons' => [],
            'warnings' => [],
            'campaign_preview' => null,
            'landing_preview' => null,
            'short_link_preview' => null,
        ];

        if (!$enabled) { $result['blocked_reasons'][] = 'feature_disabled'; return $result; }
        if ($tenantId <= 0 || $ownerUserId <= 0) { $result['blocked_reasons'][] = 'invalid_context'; return $result; }

        $name = trim((string) ($input['name'] ?? ''));
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $description = trim((string) ($input['description'] ?? ''));
        $campaignType = strtolower(trim((string) ($input['campaign_type'] ?? '')));
        $objective = trim((string) ($input['objective'] ?? ''));
        $budget = trim((string) ($input['budget'] ?? ''));
        $currency = strtoupper(trim((string) ($input['currency'] ?? '')));
        $startsAt = trim((string) ($input['starts_at'] ?? ''));
        $endsAt = trim((string) ($input['ends_at'] ?? ''));
        $landingTitle = trim((string) ($input['landing_title'] ?? ''));
        $landingSlug = trim((string) ($input['landing_slug'] ?? ''));
        $shortSlug = trim((string) ($input['short_slug'] ?? ''));

        if ($name === '') { $result['blocked_reasons'][] = 'name_required'; }
        if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,32}$/', $code)) { $result['blocked_reasons'][] = 'invalid_code'; }
        if (!in_array($campaignType, ['awareness', 'traffic', 'conversion', 'retention'], true)) { $result['blocked_reasons'][] = 'invalid_campaign_type'; }
        if ($objective === '') { $result['blocked_reasons'][] = 'objective_required'; }
        if ($currency !== '' && !preg_match('/^[A-Z]{3}$/', $currency)) { $result['blocked_reasons'][] = 'invalid_currency'; }
        if ($startsAt !== '' && !$this->isDate($startsAt)) { $result['blocked_reasons'][] = 'invalid_starts_at'; }
        if ($endsAt !== '' && !$this->isDate($endsAt)) { $result['blocked_reasons'][] = 'invalid_ends_at'; }
        if ($landingTitle === '') { $result['blocked_reasons'][] = 'landing_title_required'; }
        if ($landingSlug === '' || !$this->isSlug($landingSlug)) { $result['blocked_reasons'][] = 'invalid_landing_slug'; }
        if ($shortSlug === '' || !$this->isSlug($shortSlug)) { $result['blocked_reasons'][] = 'invalid_short_slug'; }

        if ($budget !== '' && !is_numeric($budget)) { $result['blocked_reasons'][] = 'invalid_budget'; }
        if ($description !== '' && mb_strlen($description) > 500) { $result['warnings'][] = 'description_preview_truncated'; }

        $result['campaign_preview'] = [
            'tenant_id' => $tenantId,
            'owner_user_id' => $ownerUserId,
            'name_preview' => $this->preview($name, 80),
            'code' => $code,
            'description_present' => $description !== '',
            'description_preview' => $this->preview($description, 120),
            'campaign_type' => $campaignType,
            'objective_preview' => $this->preview($objective, 80),
            'status' => 'draft',
            'budget_present' => $budget !== '',
            'currency' => $currency !== '' ? $currency : null,
            'starts_at' => $startsAt !== '' ? $startsAt : null,
            'ends_at' => $endsAt !== '' ? $endsAt : null,
        ];

        $result['landing_preview'] = [
            'title_preview' => $this->preview($landingTitle, 80),
            'slug' => $landingSlug,
            'status' => 'draft',
            'source_module' => 'campaigns',
            'source_table' => 'crm_marketing_campaigns',
            'source_id' => '[pending_campaign_id]',
        ];

        $result['short_link_preview'] = [
            'slug' => $shortSlug,
            'status' => 'inactive',
            'source_module' => 'campaigns',
            'source_table' => 'crm_marketing_campaigns',
            'source_id' => '[pending_campaign_id]',
        ];

        $result['would_create_campaign'] = $result['blocked_reasons'] === [];
        $result['would_create_landing_page'] = $result['would_create_campaign'];
        $result['would_create_short_link'] = $result['would_create_campaign'];

        return $result;
    }

    private function isDate(string $value): bool
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $value) !== false;
    }

    private function isSlug(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', strtolower($value));
    }

    private function preview(string $value, int $max): ?string
    {
        $value = trim($value);
        if ($value === '') { return null; }
        $head = mb_substr($value, 0, $max);
        return $head === $value ? $head : ($head . '…');
    }
}
