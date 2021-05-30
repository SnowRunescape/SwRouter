<?php
namespace SwRouter;

use SwRouter\Exception\RouterException;

class Router
{
    private $routes;
    
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch()
    {
        try {
            $action = $this->matchRoute();

            if ($action === false) {
                throw new RouterException("Route not found", 404);
            }
            
            $action = explode('@', $action, 2);
            
            $classname = $action[0];
            $method = $action[1] ?? 'index';

            if (class_exists($classname)) {
                $controller = new $classname();

                if (is_callable([new $controller(), $method])) {
                    call_user_func([
						new $controller(),
						$method
					]);
                } else {
                    throw new RouterException("Route not found", 404);
                }
            } else {
                throw new RouterException("Route not found", 404);
            }
        } catch (RouterException $e) {
            require 'RouterTemplate.phtml';
        } catch (\Exception $e) {
            require 'RouterTemplate.phtml';
        }
    }

    private function matchRoute()
    {
        $url = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route_uri => $route) {
            $params = [];
            
            $pattern = "@^" . preg_replace('/{[a-zA-Z0-9\_\-]+}/', '([a-zA-Z0-9\-\_]+)', $route_uri) . "$@D";
            
            $match = preg_match($pattern, $url, $params);

            if ($match) {
                return $route;
            }
        }

        return false;
    }
}