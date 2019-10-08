<?php

namespace App\Http\Middleware;

use \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Response;

use Closure;

class IsManager
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
        
        if($request->user() && $request->user()->type != "manager") {
            abort(403);
            /*
            $pagetitle = "Unauthorized";
            return Response::make(view('errors.403', compact('pagetitle')), 403);
            */
        }

        if ($request->user() && $request->user()->type == "manager") {
            return $next($request);
        }


     //   return $next($request);
        return redirect()->route('login');
    }
}
