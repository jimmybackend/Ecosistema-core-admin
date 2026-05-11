<?php

declare(strict_types=1);

namespace App\Core\Tenants;

final readonly class TenantService
{
    public const ALLOWED_STATUSES = ['active', 'trial', 'suspended', 'canceled', 'deleted'];

    public function __construct(private TenantRepository $repository)
    {
    }

    public function listTenants(): array
    {
        return $this->repository->listRecent(50);
    }

    public function findTenant(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function createTenant(array $input): bool
    {
        $data = $this->normalizeAndValidate($input);
        if ($data === null) {
            return false;
        }

        return $this->repository->create($data);
    }

    public function updateTenant(int $id, array $input): bool
    {
        $data = $this->normalizeAndValidate($input);
        if ($data === null) {
            return false;
        }

        return $this->repository->update($id, $data);
    }

    public function changeStatus(int $id, string $status): bool
    {
        $value = trim($status);
        if (!in_array($value, self::ALLOWED_STATUSES, true)) {
            return false;
        }

        return $this->repository->updateStatus($id, $value);
    }

    private function normalizeAndValidate(array $input): ?array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'trial'));
        $timezone = trim((string) ($input['timezone'] ?? 'America/Mexico_City'));
        $locale = trim((string) ($input['locale'] ?? 'es_MX'));

        if ($name === '' || $slug === '' || $timezone === '' || $locale === '') {
            return null;
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            return null;
        }

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return null;
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'legal_name' => $this->nullableTrim($input['legal_name'] ?? null),
            'domain' => $this->nullableTrim($input['domain'] ?? null),
            'plan_code' => $this->nullableTrim($input['plan_code'] ?? null),
            'status' => $status,
            'timezone' => $timezone,
            'locale' => $locale,
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }
}
