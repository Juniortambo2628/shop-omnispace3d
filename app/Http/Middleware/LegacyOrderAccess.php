<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyOrderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('admin_user')) {
            header('Location: /admin/login');
            exit;
        }

        if ((session()->get('admin_user.role', '')) === 'product_editor') {
            return redirect('/admin/products?error=Unauthorized');
        }

        return $next($request);
    }
}
