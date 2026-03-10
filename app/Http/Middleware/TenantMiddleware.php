<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $segments = explode('/', $path);


        if (!empty($segments[0])) {
            $tenantId = $segments[1];

            $tenant = Tenant::where('id', $tenantId)->first();

            if ($tenant) {
                app()->instance('currentTenant', $tenant);
            } else {
                return response()->json(['message' => 'Tenant not found'], 403);
            }
        }
        return $next($request);
    }
}
