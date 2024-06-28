<?php

namespace App\Middlewares;

use Closure;
use Illuminate\Http\Request;

class TesteMiddleware
{
public function __invoke(Request $request, Closure $next = null)
    {
        // TODO: example middleware
    }
}
