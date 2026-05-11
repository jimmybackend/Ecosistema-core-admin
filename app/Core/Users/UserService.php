<?php

declare(strict_types=1);

namespace App\Core\Users;

use PDOException;

final readonly class UserService
{
    public const ALLOWED_USER_TYPES = ['human', 'system', 'student', 'teacher', 'agent', 'admin', 'service'];
    public const ALLOWED_STATUSES = ['active', 'inactive', 'suspended', 'deleted'];

    public function __construct(private UserRepository $repository)
    {
    }

    public function listUsers(): array { return $this->repository->listRecent(100); }
    public function listTenants(): array { return $this->repository->listTenants(); }
    public function findUser(int $id): ?array { return $this->repository->findById($id); }

    public function createUser(array $input): string
    {
        $data = $this->normalizeForCreate($input);
        if ($data === null) { return 'No se pudo guardar el usuario.'; }

        try {
            return $this->repository->create($data) ? 'Usuario creado correctamente.' : 'No se pudo guardar el usuario.';
        } catch (PDOException $e) {
            if ($this->isDuplicate($e)) { return 'Ya existe un usuario con ese email o username en este tenant.'; }
            return 'No se pudo guardar el usuario.';
        }
    }

    public function updateUser(int $id, array $input): string
    {
        if ($this->repository->findById($id) === null) { return 'Usuario no encontrado.'; }
        $data = $this->normalizeForUpdate($input);
        if ($data === null) { return 'No se pudo guardar el usuario.'; }

        try {
            return $this->repository->update($id, $data) ? 'Usuario actualizado correctamente.' : 'No se pudo guardar el usuario.';
        } catch (PDOException $e) {
            if ($this->isDuplicate($e)) { return 'Ya existe un usuario con ese email o username en este tenant.'; }
            return 'No se pudo guardar el usuario.';
        }
    }

    public function updatePassword(int $id, string $password): string
    {
        if ($this->repository->findById($id) === null) { return 'Usuario no encontrado.'; }
        if (strlen($password) < 8) { return 'No se pudo guardar el usuario.'; }
        return $this->repository->updatePassword($id, password_hash($password, PASSWORD_DEFAULT))
            ? 'Contraseña actualizada correctamente.' : 'No se pudo guardar el usuario.';
    }

    public function changeStatus(int $id, string $status): string
    {
        if ($this->repository->findById($id) === null) { return 'Usuario no encontrado.'; }
        $value = trim($status);
        if (!in_array($value, self::ALLOWED_STATUSES, true)) { return 'No se pudo guardar el usuario.'; }
        return $this->repository->updateStatus($id, $value) ? 'Estado actualizado correctamente.' : 'No se pudo guardar el usuario.';
    }

    private function normalizeForCreate(array $input): ?array
    {
        $password = (string) ($input['password'] ?? '');
        if (strlen($password) < 8) { return null; }

        $base = $this->normalizeCommon($input);
        if ($base === null) { return null; }

        $base[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        return $base;
    }

    private function normalizeForUpdate(array $input): ?array
    {
        return $this->normalizeCommon($input);
    }

    private function normalizeCommon(array $input): ?array
    {
        $tenantId = (int) ($input['tenant_id'] ?? 0);
        $email = trim((string) ($input['email'] ?? ''));
        $userType = trim((string) ($input['user_type'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));

        if ($tenantId <= 0 || !$this->repository->tenantExists($tenantId)) { return null; }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) { return null; }
        if (!in_array($userType, self::ALLOWED_USER_TYPES, true) || !in_array($status, self::ALLOWED_STATUSES, true)) { return null; }

        return [
            ':tenant_id' => $tenantId,
            ':email' => $email,
            ':username' => $this->nullableTrim($input['username'] ?? null),
            ':display_name' => $this->nullableTrim($input['display_name'] ?? null),
            ':first_name' => $this->nullableTrim($input['first_name'] ?? null),
            ':last_name' => $this->nullableTrim($input['last_name'] ?? null),
            ':phone' => $this->nullableTrim($input['phone'] ?? null),
            ':user_type' => $userType,
            ':status' => $status,
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function isDuplicate(PDOException $e): bool
    {
        return isset($e->errorInfo[0]) && (string) $e->errorInfo[0] === '23000';
    }
}
