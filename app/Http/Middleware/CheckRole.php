<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $role)
    {
        if (!$request->user() || $request->user()->role->value !== $role) {
            return response()->json([
                'message' => 'You are not authorized to perform this action',
                'status' => Response::HTTP_FORBIDDEN,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
