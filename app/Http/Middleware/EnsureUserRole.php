<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403);
        }

        if ($user->status === \App\Models\User::STATUS_PENDING) {
            return redirect()->route('pending_approval');
        }

        if ($user->status !== \App\Models\User::STATUS_ACTIVE) {
            abort(403, 'Akun Anda ditangguhkan.');
        }

        return $next($request);
    }
}
