<?php

namespace App\Providers;

use Illuminate\Container\Container;

class RouteServiceProvider
{
    protected $container;
    protected $router;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register()
    {
        require APPLICATION_PATH . "/Core/routes.php";
    }
}
