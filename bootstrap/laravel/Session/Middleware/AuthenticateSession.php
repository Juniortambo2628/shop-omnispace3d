<?php

namespace Illuminate\Session\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;

/**
 * Local copy of Laravel's AuthenticateSession middleware.
 * Vendor copy is unreadable on this machine due to Windows file locks.
 */
class AuthenticateSession implements AuthenticatesSessions
{
    protected AuthManager $auth;

    /** @var callable|null */
    protected static $redirectToCallback;

    public function __construct(AuthFactory $auth)
    {
        /** @var AuthManager $auth */
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasSession() || ! $request->user() || ! $request->user()->getAuthPassword()) {
            return $next($request);
        }

        $guard = $this->guard();

        if ($guard->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($guard->getRecallerName()))[2] ?? null;

            if (! $passwordHash || ! hash_equals($request->user()->getAuthPassword(), $passwordHash)) {
                $this->logout($request);
            }
        }

        $driver = $this->auth->getDefaultDriver();

        if (! $request->session()->has('password_hash_'.$driver)) {
            $this->storePasswordHashInSession($request, $driver);
        }

        if (! hash_equals($request->session()->get('password_hash_'.$driver), $request->user()->getAuthPassword())) {
            $this->logout($request);
        }

        $response = $next($request);

        if (! is_null($this->guard()->user())) {
            $this->storePasswordHashInSession($request, $driver);
        }

        return $response;
    }

    protected function storePasswordHashInSession(Request $request, string $driver): void
    {
        if (! $request->user()) {
            return;
        }

        $request->session()->put([
            'password_hash_'.$driver => $request->user()->getAuthPassword(),
        ]);
    }

    protected function logout(Request $request): void
    {
        $this->guard()->logoutCurrentDevice();

        $request->session()->flush();

        throw new AuthenticationException(
            'Unauthenticated.',
            [$this->auth->getDefaultDriver()],
            $this->redirectTo($request)
        );
    }

    protected function guard(): SessionGuard
    {
        return $this->auth->guard();
    }

    protected function redirectTo(Request $request): ?string
    {
        if (static::$redirectToCallback) {
            return call_user_func(static::$redirectToCallback, $request);
        }

        return null;
    }

    public static function redirectUsing(callable $redirectToCallback): void
    {
        static::$redirectToCallback = $redirectToCallback;
    }
}
