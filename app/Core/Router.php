<?php
namespace App\Core;

use App\Exceptions\RouterException;

class Router
{
    private static Request $request;
    private static $routes = [];
    private static $domain = "*";

    public static function get($path, $params)
    {
        return Router::saveRoute("GET", $path, $params);
    }

    public static function post($path, $params)
    {
        return Router::saveRoute("POST", $path, $params);
    }

    public static function put($path, $params)
    {
        return Router::saveRoute("PUT", $path, $params);
    }

    public static function delete($path, $params)
    {
        return Router::saveRoute("DELETE", $path, $params);
    }

    public static function group($params, $callable)
    {
        Router::$domain = $params["domain"];

        $callable();

        Router::$domain = "*";
    }

    public static function dispatch()
    {
        Router::loadRoutes();

        $route = Router::matchRoute();

        if ($route === false) {
            throw new RouterException("Route not found", 404);
        }

        $controller = $route->controller;

        $classname = $controller[0];
        $method = $controller[1] ?? "index";

        if (!class_exists($classname)) {
            throw new RouterException("Route not found", 404);
        }

        $controller = new $classname();

        if (!is_callable([$controller, $method])) {
            throw new RouterException("Route not found", 404);
        }

        Router::$request = new Request();
        
        Router::middleware($route->middlewares);

        call_user_func([$controller, $method], Router::$request);
    }

    private static function middleware($middlewares)
    {
        if (count($middlewares) > 0) {
            foreach ($middlewares as $middleware) {
                if (!class_exists($middleware)) {
                    throw new RouterException("Middleware {$middleware} not found", 404);
                }
                
                $class = new $middleware();

                if (!is_callable([$class, "__invoke"])) {
                    throw new RouterException("Route not found", 404);
                }

                call_user_func([$class, "__invoke"], Router::$request, null);
            }
        }
    }

    private static function matchRoute()
    {
        $routes = [];

        if (array_key_exists($_SERVER["HTTP_HOST"], Router::$routes)) {
            $routes = array_merge($routes, Router::$routes[$_SERVER["HTTP_HOST"]]);
        }

        if (array_key_exists($_SERVER["REQUEST_METHOD"], $routes)) {
            $routes = array_merge($routes, $routes[$_SERVER["REQUEST_METHOD"]]);
        }
        
        if (array_key_exists("*", Router::$routes) && array_key_exists($_SERVER["REQUEST_METHOD"], Router::$routes["*"])) {
            $routes = array_merge(Router::$routes["*"][$_SERVER["REQUEST_METHOD"]], $routes);
        }

        $url = Router::getPath(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

        foreach ($routes as $route_uri => $route) {
            $params = [];

            $pattern = "@^" . preg_replace("/:[a-zA-Z0-9\_\-]+/", "([a-zA-Z0-9\-\_]+)", $route_uri) . "$@D";

            if (preg_match($pattern, $url, $params)) {
                return $route;
            }
        }

        return false;
    }

    private static function saveRoute($method, $path, $controller)
    {
        $path = Router::getPath($path);

        $route = new Route($controller);

        Router::$routes[Router::$domain][$method][$path] = $route;

        return $route;
    }

    private static function loadRoutes()
    {
        require_once "Routes.php";
    }

    private static function getPath($path)
    {
        $path = rtrim($path, "/");

        return ($path == "") ? "/" : $path;
    }
}
