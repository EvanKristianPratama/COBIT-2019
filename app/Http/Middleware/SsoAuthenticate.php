<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SsoAuthenticate
{
    /**
     * Handle an incoming request.
     * 
     * Jika user belum login, redirect ke SSO Server dengan callback URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Encode callback URL untuk parameter
            $callbackUrl = urlencode(url('/sso/callback'));
            $ssoUrl = config('sso.server_url') . '/login?redirect=' . $callbackUrl;
            
            return redirect($ssoUrl);
        }

        return $next($request);
    }
}
