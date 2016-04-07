<?php

namespace Rig\Track;

/**
 * Class Router
 * @package Rig\Track
 */
class Router
{
    protected static $patternAliases = array(
        '/{(.+?):numeric}/' => '{$1:[0-9]+}',
        '/{(.+?):alpha}/' => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanumeric}/' => '{$1:[a-zA-Z0-9]+}',
        '/{(.+?):any}/' => '{$1:[a-zA-Z0-9$-_.+!*\'(),]+}',
    );

    protected $currentRoute;

    protected $routes;

    protected $routeMap = array();

    public function __construct()
    {

    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes($routes)
    {
        $this->routes = $routes;
        return $this;
    }

    public function compile()
    {
        $this->register($this->getRoutes());
        return $this;
    }

    public function register(\Rig\Track\Group $group)
    {
        foreach ($group->getRoutes() as $key => $route) {
            if ($route instanceof \Rig\Track\Group) {
                $this->register($route);
                continue;
            }
            $this->addRoute($route, $key);
        }
        return $this;
    }

    /**
     * @param $pattern
     * @return mixed
     */
    protected function expandPatternAlias($pattern)
    {
        return preg_replace(array_keys(self::$patternAliases), array_values(self::$patternAliases), $pattern);
    }

    protected function addRoute(\Rig\Track\Route $route, $key = null)
    {
        $expanded = $this->expandPatternAlias($route->getPattern());
        $route->setPattern($expanded);
        if (!is_int($key)) {
            $route->overrideUid($key);
            $this->routes[$key] = $route;
            return $this;
        }
        $this->routeMap[$route->getUid()] = $route;
        return $this;
    }

    public function resolve(\Rig\HTTP\Request $request) : \Rig\Track\Route
    {
        return $this->route($this->getCompiledRoutes(), $request->getMethod(), $request->getUri()->get('path'));
    }

    public function getCompiledRoutes()
    {
        $compiledRoutes = array();
        foreach ($this->routeMap as $route) {
            $compiledRoutes[] = array(
                'methods' => $route->getMethods(),
                'pattern' => $route->getPattern(),
                'uid' => $route->getUid()
            );
        }
        return $compiledRoutes;
    }

    /**
     * @param $routeCallback
     * @param array $options
     * @return mixed
     */
    public function simpleDispatcher($routeCallback, array $options = array())
    {
        /** @noinspection AdditionOperationOnArraysInspection */
        $options += array(
            'routeParser' => 'FastRoute\\RouteParser\\Std',
            'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
            'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
        );

        $routeCollector = new \FastRoute\RouteCollector(new $options['routeParser'], new $options['dataGenerator']);
        $routeCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }

    protected function route($compiledRoutes, $method, $uri)
    {
        $fastDispatcher = $this->simpleDispatcher(function (\FastRoute\RouteCollector $r) use ($compiledRoutes) {
            foreach ($compiledRoutes as $route) {
                $r->addRoute($route['methods'], $route['pattern'], $route['uid']);
            }
        });

        $routeInfo = $fastDispatcher->dispatch($method, $uri);

        if ($routeInfo[0] === \FastRoute\Dispatcher::NOT_FOUND) {
            throw new \Rig\Track\Exception\NotFound($method, $uri);
        } elseif ($routeInfo[0] === \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            throw new \Rig\Track\Exception\MethodNotAllowed($method, $uri);
        } elseif ($routeInfo[0] !== \FastRoute\Dispatcher::FOUND) {
            throw new \RuntimeException;
        }

        $route = $this->routeMap[$routeInfo[1]];
        $route->setParams($routeInfo[2]);
        $this->setCurrentRoute($route);
        return $route;
    }

    /**
     * @return \Rig\Track\Route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    /**
     * @param mixed $currentRoute
     */
    public function setCurrentRoute($currentRoute)
    {
        $this->currentRoute = $currentRoute;
    }
}
