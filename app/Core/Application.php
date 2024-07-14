<?php

namespace App\Core;

use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Illuminate\Routing\RoutingServiceProvider;
use Illuminate\Support\Facades\Route;

class Application extends Container
{
    public function __construct()
    {
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance("app", $this);

        $this->instance(Container::class, $this);

        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this);
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        (new EventServiceProvider($this))->register();
        (new RoutingServiceProvider($this))->register();
    }

    public static function configure()
    {
        return new static;
    }

    protected function route(): Router
    {
        return $this->make("router");
    }

    /**
     * Register the routing services for the application.
     *
     * @param  array  $routes
     * @return $this
     */
    public function withRouting(array $routes = [])
    {
        foreach ($routes as $group => $route) {
            $this->route()->middlewareGroup($group, []);

            Route::middleware($group)->group(function () use ($route) {
                require $route;
            });
        }

        return $this;
    }

    /**
     * Register the global middleware, middleware groups, and middleware aliases for the application.
     *
     * @param  array  $middlewareGroups
     * @return $this
     */
    public function withMiddleware(array $middlewareGroups = [])
    {
        foreach ($middlewareGroups as $group => $middlewares) {
            foreach ($middlewares as $middleware) {
                $this->route()->pushMiddlewareToGroup($group, $middleware);
            }
        }

        return $this;
    }

    public function create()
    {
        return $this;
    }

    public function handleRequest(Request $request)
    {
        $this->route()->dispatch($request)->send();
    }
}
