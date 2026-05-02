<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        $allowedAdminEmails = array_filter([
            config('app.admin_login'),
            config('app.super_admin_login'),
        ]);

        if (! in_array($user->email, $allowedAdminEmails, true)) {
            abort(403);
        }

        return $next($request);
    }
}
