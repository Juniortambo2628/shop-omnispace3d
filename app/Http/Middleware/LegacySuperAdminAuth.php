<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacySuperAdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        require_once base_path('core/Auth.php');

        \Auth::requireSuperAdmin();

        return $next($request);
    }
}
