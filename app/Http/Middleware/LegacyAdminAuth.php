<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyAdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        require_once base_path('core/Auth.php');

        \Auth::requireAdmin();

        return $next($request);
    }
}
