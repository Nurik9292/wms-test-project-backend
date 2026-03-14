<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Tenant not found.'], 403);
        }

        if (!$user->tenant || !$user->tenant->is_active) {
            return response()->json(['message' => 'Tenant is inactive.'], 403);
        }

        app()->instance('current_tenant_id', $user->tenant_id);

        return $next($request);
    }
}
