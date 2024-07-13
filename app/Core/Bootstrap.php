<?php

namespace App\Core;

use App\Providers\RouteServiceProvider;
use Dotenv\Dotenv;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;

class Bootstrap
{
    protected Container $container;

    protected function __construct()
    {
        $this->container = Container::getInstance();

        $this->setupConstants();
        $this->setupEnvs();
        $this->setupDatabase();
        $this->setupFacade();
        $this->setupRequest();
        $this->setupRouter();
        $this->registerProviders();
    }

    public static function init()
    {
        return new static;
    }

    protected function setupConstants()
    {
        define("APPLICATION_START", microtime(true));
        define("ROOT_PATH", __DIR__ . "/../../");
        define("APPLICATION_PATH", ROOT_PATH . "/app");
    }

    protected function setupEnvs()
    {
        Dotenv::createUnsafeImmutable(ROOT_PATH)->safeLoad();
    }

    protected function setupDatabase()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            "driver" => "mysql",
            "host" => getenv("DATABASE_HOST"),
            "database" => getenv("DATABASE_DB"),
            "username" => getenv("DATABASE_USERNAME"),
            "password" => getenv("DATABASE_PASSWORD"),
        ]);
        $capsule->setAsGlobal();
        $capsule->setEventDispatcher(new Dispatcher($this->container));
        $capsule->bootEloquent();
    }

    protected function setupFacade()
    {
        Facade::setFacadeApplication($this->container);
    }

    protected function setupRequest()
    {
        $this->container->singleton(Request::class, function () {
            return Request::capture();
        });
    }

    protected function setupRouter()
    {
        $events = new Dispatcher($this->container);
        $router = new Router($events, $this->container);
        $this->container->instance("router", $router);
    }

    protected function registerProviders()
    {
        (new RouteServiceProvider($this->container))->register();
    }
}
