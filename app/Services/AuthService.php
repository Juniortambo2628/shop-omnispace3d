<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuthService
{
    public function check(): bool
    {
        return session()->has('admin_user');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return session()->get('admin_user');
    }

    public function isSuperAdmin(): bool
    {
        return (session()->get('admin_user.role', '')) === 'super_admin';
    }

    public function requireAdmin(): void
    {
        if (! $this->check()) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function requireSuperAdmin(): void
    {
        $this->requireAdmin();

        if (! $this->isSuperAdmin()) {
            header('Location: /admin/orders?error=Unauthorized');
            exit;
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function attemptLogin(string $username, string $password): bool
    {
        $username = trim($username);

        $query = AdminUser::query()->where('active', true);

        if (Schema::hasColumn('admin_users', 'email')) {
            $query->where(function ($builder) use ($username) {
                $builder->where('username', $username)->orWhere('email', $username);
            });
        } else {
            $query->where('username', $username);
        }

        $model = $query->first();
        $user = $model ? $model->toLegacyArray() : null;
        $hash = $model?->passwordHash();

        if ($user && $hash && password_verify($password, $hash)) {
            $identity = ! empty($user['email']) ? $user['email'] : $user['username'];
            session()->put('admin_user', [
                'id' => (int) $user['id'],
                'username' => $identity,
                'role' => $user['role'],
            ]);
            Log::info('Admin login successful', [
                'user' => $identity,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);

            return true;
        }

        Log::error('Failed admin login attempt', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        return false;
    }

    public function logout(): void
    {
        Log::info('Admin logout', ['user' => session()->get('admin_user.username', 'unknown')]);
        session()->forget('admin_user');
    }

    /**
     * @return array{current_username: string, current_role: string}
     */
    public function viewContext(): array
    {
        return [
            'current_username' => session()->get('admin_user.username', ''),
            'current_role' => session()->get('admin_user.role', ''),
        ];
    }

    public function updateSessionIdentity(string $identity): void
    {
        if (! $this->check()) {
            return;
        }

        session()->put('admin_user.username', $identity);
    }

    public function defaultLandingPath(): string
    {
        if ($this->isSuperAdmin() || (session()->get('admin_user.role', '') === 'order_manager')) {
            return '/admin/orders';
        }

        return '/admin/products';
    }
}
