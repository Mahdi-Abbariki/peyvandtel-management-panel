<?php

namespace App\Http\Middleware;

use App\Models\PeyvandtelAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeyvandtelAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user instanceof PeyvandtelAdmin)
            return $next($request);
        else
            abort(Response::HTTP_UNAUTHORIZED);
    }
}
