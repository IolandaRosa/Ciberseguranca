<?php

namespace App\Http\Middleware;

use Closure;

class IsCashier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && $request->user()->type == 'cashier') {
            return $next($request);
        }

        return Response::json([
            'unauthorized' => 'Unauthorized Access! Only cashiers are allowed!'
        ], 401);
    }
}
