<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Schema;

class AdminUserService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listUsers(): array
    {
        return AdminUser::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (AdminUser $user) => $user->toLegacyArray())
            ->all();
    }

    public function createUser(string $displayName, string $username, string $password, string $role): void
    {
        $payload = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'display_name' => $displayName,
            'role' => $role,
            'active' => true,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        if ($this->hasEmailColumn()) {
            $payload['email'] = $username;
        }

        AdminUser::create($payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdentity(string $identity): ?array
    {
        $identity = trim($identity);

        if ($identity === '') {
            return null;
        }

        $query = AdminUser::query()->where('active', true);

        if ($this->hasEmailColumn()) {
            $query->where(function ($builder) use ($identity) {
                $builder->where('username', $identity)->orWhere('email', $identity);
            });
        } else {
            $query->where('username', $identity);
        }

        $user = $query->first();

        return $user ? $user->toLegacyArray() : null;
    }

    /**
     * @return array{display_name: string, email: string, username: string, role: string, created_at: string|null}
     */
    public function profileViewData(array $user): array
    {
        $email = $user['email'] ?? $user['username'] ?? '';

        return [
            'display_name' => $user['display_name'] ?? $user['username'] ?? '',
            'email' => $email,
            'username' => $user['username'] ?? $email,
            'role' => $user['role'] ?? 'admin',
            'created_at' => $user['created_at'] ?? null,
        ];
    }

    public function updateProfile(int $userId, string $displayName, string $email): void
    {
        $displayName = trim($displayName);
        $email = trim($email);

        if ($displayName === '') {
            throw new \InvalidArgumentException('Display name is required.');
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A valid email address is required.');
        }

        if ($this->emailTakenByOther($email, $userId)) {
            throw new \InvalidArgumentException('That email is already in use by another account.');
        }

        $user = AdminUser::find($userId);

        if (! $user) {
            throw new \InvalidArgumentException('Account not found.');
        }

        if ($this->hasEmailColumn()) {
            $user->update([
                'display_name' => $displayName,
                'email' => $email,
                'username' => $email,
            ]);

            return;
        }

        if ($this->usernameTakenByOther($email, $userId)) {
            throw new \InvalidArgumentException('That email is already in use by another account.');
        }

        $user->update([
            'display_name' => $displayName,
            'username' => $email,
        ]);
    }

    public function updatePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('New password must be at least 8 characters.');
        }

        $user = AdminUser::query()->where('id', $userId)->where('active', true)->first();

        if (! $user) {
            throw new \InvalidArgumentException('Account not found.');
        }

        $hash = $user->passwordHash();

        if (! $hash || ! password_verify($currentPassword, $hash)) {
            throw new \InvalidArgumentException('Current password is incorrect.');
        }

        $user->update([
            $this->passwordColumn() => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }

    public static function initialsFromIdentity(string $identity): string
    {
        $identity = trim($identity);

        if ($identity === '') {
            return 'A';
        }

        if (str_contains($identity, '@')) {
            $local = explode('@', $identity, 2)[0];
            $parts = preg_split('/[._\-+]+/', $local, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if (count($parts) >= 2) {
                return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
            }

            return strtoupper(substr($local, 0, 1));
        }

        return strtoupper(substr($identity, 0, 1));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $user = AdminUser::find($id);

        return $user ? $user->toLegacyArray() : null;
    }

    public function updateUser(int $id, string $displayName, string $email, string $role, bool $active): void
    {
        $displayName = trim($displayName);
        $email = trim($email);
        $allowedRoles = ['super_admin', 'product_editor', 'order_manager'];

        if ($displayName === '') {
            throw new \InvalidArgumentException('Display name is required.');
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A valid email address is required.');
        }

        if (! in_array($role, $allowedRoles, true)) {
            throw new \InvalidArgumentException('Invalid role selected.');
        }

        $user = AdminUser::find($id);

        if (! $user) {
            throw new \InvalidArgumentException('User not found.');
        }

        if ($this->emailTakenByOther($email, $id)) {
            throw new \InvalidArgumentException('That email is already in use by another account.');
        }

        if ($this->hasEmailColumn()) {
            $user->update([
                'display_name' => $displayName,
                'email' => $email,
                'username' => $email,
                'role' => $role,
                'active' => $active,
            ]);

            return;
        }

        $user->update([
            'display_name' => $displayName,
            'username' => $email,
            'role' => $role,
            'active' => $active,
        ]);
    }

    public function adminSetPassword(int $id, string $newPassword): void
    {
        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }

        $user = AdminUser::find($id);

        if (! $user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $user->update([
            $this->passwordColumn() => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }

    public function setActive(int $id, bool $active): void
    {
        $user = AdminUser::find($id);

        if (! $user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $user->update(['active' => $active]);
    }

    private function hasEmailColumn(): bool
    {
        return Schema::hasColumn('admin_users', 'email');
    }

    private function passwordColumn(): string
    {
        if (Schema::hasColumn('admin_users', 'password_hash')) {
            return 'password_hash';
        }

        return 'password';
    }

    private function emailTakenByOther(string $email, int $userId): bool
    {
        if ($this->hasEmailColumn()) {
            return AdminUser::query()
                ->where('id', '!=', $userId)
                ->where(function ($query) use ($email) {
                    $query->where('username', $email)->orWhere('email', $email);
                })
                ->exists();
        }

        return $this->usernameTakenByOther($email, $userId);
    }

    private function usernameTakenByOther(string $username, int $userId): bool
    {
        return AdminUser::query()
            ->where('id', '!=', $userId)
            ->where('username', $username)
            ->exists();
    }
}
