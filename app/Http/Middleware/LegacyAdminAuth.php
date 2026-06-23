<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyAdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('admin_user')) {
            header('Location: /admin/login');
            exit;
        }

        return $next($request);
    }
}
