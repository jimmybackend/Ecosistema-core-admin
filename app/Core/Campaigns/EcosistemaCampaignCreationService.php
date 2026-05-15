<?php

declare(strict_types=1);

namespace App\Core\Campaigns;

final readonly class EcosistemaCampaignCreationService
{
    public function __construct(private EcosistemaCampaignCreationRepository $repository, private array $config = []) {}

    public function create(int $tenantId, int $ownerUserId, array $input): array
    {
        $result = [
            'mode' => 'controlled-write',
            'write_enabled' => (bool) ($this->config['campaign_creation_write'] ?? false),
            'landing_draft_enabled' => (bool) ($this->config['campaign_create_landing_draft'] ?? false),
            'short_link_enabled' => (bool) ($this->config['campaign_create_short_link'] ?? false),
            'created' => false,
            'campaign_id' => null,
            'blocked_reasons' => [],
            'warnings' => [],
        ];

        if (!$result['write_enabled']) { $result['blocked_reasons'][] = 'feature_disabled'; return $result; }
        if ($tenantId <= 0 || $ownerUserId <= 0) { $result['blocked_reasons'][] = 'invalid_context'; return $result; }

        $data = $this->validateAndNormalize($input, $result['blocked_reasons']);
        if ($result['blocked_reasons'] !== []) { return $result; }

        $this->repository->beginTransaction();
        try {
            $campaignId = $this->repository->createCampaign($tenantId, $data + ['owner_user_id' => $ownerUserId]);
            $this->repository->commit();
            $result['created'] = $campaignId > 0;
            $result['campaign_id'] = $campaignId > 0 ? $campaignId : null;
            if ($result['landing_draft_enabled']) { $result['warnings'][] = 'landing_draft_flag_enabled_but_not_implemented_in_pr133'; }
            if ($result['short_link_enabled']) { $result['warnings'][] = 'short_link_flag_enabled_but_not_implemented_in_pr133'; }
        } catch (\Throwable) {
            $this->repository->rollBack();
            $result['blocked_reasons'][] = 'write_failed';
        }

        return $result;
    }

    private function validateAndNormalize(array $input, array &$blockedReasons): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $description = trim((string) ($input['description'] ?? ''));
        $campaignType = strtolower(trim((string) ($input['campaign_type'] ?? '')));
        $objective = trim((string) ($input['objective'] ?? ''));
        $budget = trim((string) ($input['budget'] ?? ''));
        $currency = strtoupper(trim((string) ($input['currency'] ?? '')));
        $startsAt = trim((string) ($input['starts_at'] ?? ''));
        $endsAt = trim((string) ($input['ends_at'] ?? ''));

        if ($name === '') { $blockedReasons[] = 'name_required'; }
        if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,32}$/', $code)) { $blockedReasons[] = 'invalid_code'; }
        if (!in_array($campaignType, ['awareness', 'traffic', 'conversion', 'retention'], true)) { $blockedReasons[] = 'invalid_campaign_type'; }
        if ($objective === '') { $blockedReasons[] = 'objective_required'; }
        if ($currency !== '' && !preg_match('/^[A-Z]{3}$/', $currency)) { $blockedReasons[] = 'invalid_currency'; }
        if ($startsAt !== '' && !self::isDate($startsAt)) { $blockedReasons[] = 'invalid_starts_at'; }
        if ($endsAt !== '' && !self::isDate($endsAt)) { $blockedReasons[] = 'invalid_ends_at'; }
        if ($budget !== '' && !is_numeric($budget)) { $blockedReasons[] = 'invalid_budget'; }

        return [
            'name' => $name,
            'code' => $code,
            'description' => $description === '' ? null : $description,
            'campaign_type' => $campaignType,
            'objective' => $objective,
            'budget' => $budget === '' ? null : $budget,
            'currency' => $currency === '' ? null : $currency,
            'starts_at' => $startsAt === '' ? null : $startsAt,
            'ends_at' => $endsAt === '' ? null : $endsAt,
        ];
    }

    private static function isDate(string $value): bool
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $value) !== false;
    }
}
