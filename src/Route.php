<?php

namespace Rig\Track;

/**
 * Class Route
 * @package Rig\Track
 */
class Route implements \Rig\Track\RoutingEntity
{
    // Keep references to routes
    protected static $idIndex = 0;
    protected $uid;

    protected $methods;
    protected $pattern;
    protected $callable;

    protected $beforeCallables = array();
    protected $afterCallables = array();

    protected $params = array();

    protected $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'CONNECT',
        'TRACE',
        'PATCH',
        'OPTIONS',
    ];

    public function __construct($methods, $pattern, $callable)
    {
        $this->setMethods($methods);
        $this->setPattern($pattern);
        $this->setCallable($callable);

        static::$idIndex++;
        $this->uid = static::$idIndex;

        return $this;
    }

    /**
     * Overrides route unique id
     * @param  string|int $uid
     */
    public function overrideUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Returns route's name/unique id
     * @return string|int
     */
    public function getUid()
    {
        return $this->uid;
    }

    public function before($beforeCallable)
    {
        $this->beforeCallables[] = $beforeCallable;
        return $this;
    }

    public function after($afterCallable)
    {
        $this->afterCallables[] = $afterCallable;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @param mixed $callable
     */
    public function setCallable($callable)
    {
        $this->callable = $callable;
    }

    /**
     * @return mixed
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * @param mixed $methods
     */
    public function setMethods($methods)
    {
        if (is_array($methods)) {
            $this->methods = $methods;
            foreach ($methods as $method) {
                if (!in_array($method, $this->allowedMethods)) {
                    throw new \InvalidArgumentException('Disallowed HTTP method used: '.$method);
                }
            }
            return;
        }
        if (!in_array($methods, $this->allowedMethods)) {
            throw new \InvalidArgumentException('Disallowed HTTP method used: '.$methods);
        }
        $this->methods[] = $methods;
    }

    /**
     * @return mixed
     */
    public function getPattern() : string
    {
        return $this->pattern;
    }

    /**
     * @param mixed $pattern
     */
    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return array
     */
    public function getAllowedMethods() : array
    {
        return $this->allowedMethods;
    }

    public function build(array $params) : string
    {

    }

    public function setPrefix($prefix)
    {
        $this->setPattern($prefix.$this->getPattern());
        return $this;
    }


    /**
     * Get route parameters
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set route parameters
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }


    /**
     * @return array
     */
    public function getAfterCallables()
    {
        return $this->afterCallables;
    }

    /**
     * @return array
     */
    public function getBeforeCallables()
    {
        return $this->beforeCallables;
    }
}
