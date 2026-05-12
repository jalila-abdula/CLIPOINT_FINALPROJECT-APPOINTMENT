<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToRolePortal
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->role !== $role) {
            return $this->redirectForRole($user->role);
        }

        return $next($request);
    }

    private function redirectForRole(string $role): RedirectResponse
    {
        return redirect()->route(match ($role) {
            User::ROLE_ADMIN => 'portal.admin',
            User::ROLE_RECEPTIONIST => 'portal.receptionist',
            User::ROLE_STAFF => 'portal.staff',
            default => 'login',
        });
    }
}
