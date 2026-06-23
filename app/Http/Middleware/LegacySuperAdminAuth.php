<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacySuperAdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('admin_user')) {
            header('Location: /admin/login');
            exit;
        }

        if ((session()->get('admin_user.role', '')) !== 'super_admin') {
            header('Location: /admin/orders?error=Unauthorized');
            exit;
        }

        return $next($request);
    }
}
