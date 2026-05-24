<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Schema;

class AuthService
{
    public function __construct()
    {
        require_once BASE_PATH . '/core/Auth.php';
    }

    public function check(): bool
    {
        return \Auth::check();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return \Auth::user();
    }

    public function isSuperAdmin(): bool
    {
        return \Auth::isSuperAdmin();
    }

    public function requireAdmin(): void
    {
        \Auth::requireAdmin();
    }

    public function requireSuperAdmin(): void
    {
        \Auth::requireSuperAdmin();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function attemptLogin(string $username, string $password, array $config): bool
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
            \Auth::login($identity, $user['role'], (int) $user['id']);
            \Log::info('Admin login successful', [
                'user' => $identity,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);

            return true;
        }

        if (
            (empty($username) || strtolower($username) === 'admin')
            && $password === ($config['admin_password'] ?? '')
        ) {
            \Auth::login('admin', 'super_admin', 0);

            return true;
        }

        \Log::error('Failed admin login attempt', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        return false;
    }

    public function logout(): void
    {
        \Log::info('Admin logout', ['user' => \Auth::user()['username'] ?? 'unknown']);
        \Auth::logout();
    }

    /**
     * @return array{current_username: string, current_role: string}
     */
    public function viewContext(): array
    {
        return [
            'current_username' => \Auth::user()['username'] ?? '',
            'current_role' => \Auth::user()['role'] ?? '',
        ];
    }

    public function updateSessionIdentity(string $identity): void
    {
        if (! \Auth::check()) {
            return;
        }

        $_SESSION['admin_user']['username'] = $identity;
    }

    public function defaultLandingPath(): string
    {
        return \Auth::defaultLandingPath();
    }
}
