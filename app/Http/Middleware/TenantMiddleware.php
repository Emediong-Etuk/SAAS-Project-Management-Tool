<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $path=$request->path();
        $segments=explode('/',$path);
        Log::info('segments',[$segments]);

        if(!empty($segments[0])){
            $tenantId=$segments[1];
            Log::info('tenantId',[$tenantId]);

            $tenant=Tenant::where('id',$tenantId)->first();

            if($tenant){
                // Set tenant to be available globally
                app()->instance('currentTenant', $tenant);
            }else{
                return response()->json(['message'=>'Tenant not found'],404);
            }
        }
        return $next($request);
    }
}
