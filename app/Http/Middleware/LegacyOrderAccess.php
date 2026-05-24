<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyOrderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        require_once base_path('core/Auth.php');

        \Auth::requireAdmin();

        if ((\Auth::user()['role'] ?? '') === 'product_editor') {
            return redirect('/admin/products?error=Unauthorized');
        }

        return $next($request);
    }
}
