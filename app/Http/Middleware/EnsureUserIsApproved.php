<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->approval_status === 'pending') {
                // If the user visits the approval notice page or logout, let them pass
                if ($request->routeIs('approval.notice') || $request->routeIs('logout')) {
                    return $next($request);
                }
                return redirect()->route('approval.notice');
            }

            if ($user->approval_status === 'rejected') {
                if ($request->routeIs('approval.rejected') || $request->routeIs('logout')) {
                    return $next($request);
                }
                // Determine if we show a specific rejected page or generic notice
                return redirect()->route('approval.notice')->with('status', 'rejected');
            }
        }

        return $next($request);
    }
}
