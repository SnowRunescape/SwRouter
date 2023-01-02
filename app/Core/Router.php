<?php

namespace App\Core;

use App\Exceptions\HttpResponseException;
use App\Exceptions\RouterException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Throwable;

class Router
{
    private static Request $request;
    private static $routes = [];
    private static $domain = "*";
    private static $prefix = "";
    private static $middlewares = [];

    public static function get($path, $params)
    {
        return self::saveRoute("GET", $path, $params);
    }

    public static function post($path, $params)
    {
        return self::saveRoute("POST", $path, $params);
    }

    public static function path($path, $params)
    {
        return self::saveRoute("PATH", $path, $params);
    }

    public static function put($path, $params)
    {
        return self::saveRoute("PUT", $path, $params);
    }

    public static function delete($path, $params)
    {
        return self::saveRoute("DELETE", $path, $params);
    }

    public static function any($path, $params)
    {
        return self::saveRoute("ANY", $path, $params);
    }

    public static function group($params, $callable)
    {
        $t_domain = self::$domain;
        $t_prefix = self::$prefix;
        $t_middlewares = self::$middlewares;

        if (isset($params["domain"])) {
            self::$domain = $params["domain"];
        }

        if (isset($params["prefix"])) {
            $prefix = $t_prefix;

            if (!str_starts_with($params["prefix"], "/")) {
                $params["prefix"] = "/{$params["prefix"]}";
            }

            $prefix .= $params["prefix"];

            self::$prefix = $prefix;
        }

        if (isset($params["middleware"])) {
            self::$middlewares = array_merge(self::$middlewares, $params["middleware"]);
        }

        $callable();

        self::$domain = $t_domain;
        self::$prefix = $t_prefix;
        self::$middlewares = $t_middlewares;
    }

    public static function dispatch()
    {
        try {
            self::$request = new Request();

            self::loadRoutes();

            $route = self::matchRoute();

            if ($route === false) {
                throw new RouterException("Route not found", 404);
            }

            self::$request->_input = new ParameterBag($route->params);

            $controller = $route->controller;

            $classname = $controller[0];
            $method = $controller[1] ?? "index";

            if (!class_exists($classname)) {
                throw new RouterException("Route {$classname} not found", 404);
            }

            $controller = new $classname(self::$request);

            if (!is_callable([$controller, $method])) {
                throw new RouterException("Route not found", 404);
            }

            self::middleware($route->middlewares);

            call_user_func([$controller, $method]);
        } catch (RouterException | HttpResponseException $e) {

        } catch (Throwable $e) {

        }
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

                call_user_func([$class, "__invoke"], self::$request, null);
            }
        }
    }

    private static function matchRoute()
    {
        $routes = [];

        if (array_key_exists($_SERVER["HTTP_HOST"], self::$routes)) {
            $routes = array_merge($routes, self::$routes[$_SERVER["HTTP_HOST"]]);
        }

        if (array_key_exists($_SERVER["REQUEST_METHOD"], $routes)) {
            $routes = array_merge($routes, $routes[$_SERVER["REQUEST_METHOD"]]);
        }

        if (array_key_exists("ANY", $routes)) {
            $routes = array_merge($routes, $routes["ANY"]);
        }

        if (array_key_exists("*", self::$routes) && count($routes) == 0) {
            if (array_key_exists($_SERVER["REQUEST_METHOD"], self::$routes["*"])) {
                $routes = array_merge(self::$routes["*"][$_SERVER["REQUEST_METHOD"]], $routes);
            }

            if (array_key_exists("ANY", self::$routes["*"])) {
                $routes = array_merge(self::$routes["*"]["ANY"], $routes);
            }
        }

        $url = self::getPath(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

        foreach ($routes as $route_uri => $route) {
            $params = [];

            $pattern = "@^" . preg_replace("/:[a-zA-Z0-9\_\-.]+/", "([a-zA-Z0-9\-\_.]+)", $route_uri) . "$@D";

            if (preg_match($pattern, $url, $params)) {
                return $route;
            }
        }

        return false;
    }

    private static function saveRoute($method, $path, $controller)
    {
        $path = self::getPath($path);

        $route = new Route($controller);

        if (self::$middlewares) {
            foreach (self::$middlewares as $middleware) {
                $route->middleware($middleware);
            }
        }

        self::$routes[self::$domain][$method][$path] = $route;

        return $route;
    }

    private static function loadRoutes()
    {
        require_once "Routes.php";
    }

    private static function getPath($path)
    {
        if (self::$prefix != "") {
            $prefix = self::$prefix;

            if (!str_starts_with($path, "/")) {
                $path = "/{$path}";
            }

            $path = "{$prefix}{$path}";
        }

        $path = rtrim($path, "/");

        return ($path == "") ? "/" : $path;
    }
}
