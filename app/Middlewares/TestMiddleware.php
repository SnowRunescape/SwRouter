<?php

namespace App\Middlewares;

use Closure;
use Illuminate\Http\Request;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $next($request);
    }
}
