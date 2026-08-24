<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Menolak (403) setiap request yang bukan dari user ber-role admin.
     * Didaftarkan sebagai alias 'admin' di bootstrap/app.php.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== UserRole::Admin) {
            abort(403, 'Hanya admin yang dapat mengakses fitur ini.');
        }

        return $next($request);
    }
}
