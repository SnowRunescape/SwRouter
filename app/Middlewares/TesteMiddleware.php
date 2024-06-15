<?php

namespace App\Middlewares;

use App\Core\Request;
use Closure;

class TesteMiddleware
{
    public function __invoke(Request $request, Closure $next = null)
    {
        // TODO: example middleware
    }
}
