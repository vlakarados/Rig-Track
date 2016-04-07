<?php

namespace Rig\Track;

/**
 * Class Group
 * @package Rig\Track
 */
class Group implements \Rig\Track\RoutingEntity
{
    protected $routes = array();

    protected $beforeCallables = array();
    protected $afterCallables = array();

    /**
     * @param array $args
     */
    public function __construct(...$args)
    {
        if (!count($args)) {
            return [];
        }
        $routes = array_pop($args);
        foreach ($routes as $key => $route) {
            if (!($route instanceof \Rig\Track\RoutingEntity)) {
                throw new \InvalidArgumentException(
                    'All routes should be instances of \Rig\Track\RoutingEntity'
                );
            }
            $this->routes[$key] = $route;
        }
        if (count($args)) {
            $options = array_pop($args);
            $this->applyOptions($options);
        }
    }

    /**
     * Method for expanding a group
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function before($beforeCallable)
    {
        foreach ($this->routes as $route) {
            $route->before($beforeCallable);
        }
        return $this;
    }

    public function after($afterCallable)
    {
        foreach ($this->routes as $route) {
            $route->after($afterCallable);
        }
        return $this;
    }

    public function setPrefix($prefix)
    {
        foreach ($this->routes as $route) {
            $route->setPrefix($prefix);
        }
        return $this;
    }

    protected function applyOptions($options)
    {
        if (array_key_exists('prefix', $options)) {
            $this->setPrefix($options['prefix']);
        }
        if (array_key_exists('before', $options)) {
            $this->before($options['before']);
        }
        if (array_key_exists('after', $options)) {
            $this->after($options['after']);
        }
    }
}
