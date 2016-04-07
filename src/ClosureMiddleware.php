<?php

namespace Rig\Track;

class ClosureMiddleware implements \Rig\Track\Middleware
{
    protected $callable;

    protected $request;
    protected $response;

    public function __construct(\Rig\HTTP\Response $response, \Rig\HTTP\Request $request)
    {
        $this->setRequest($request);
        $this->setResponse($response);
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $request
     * @return ClosureMiddleware|Middleware
     */
    public function setRequest($request) : \Rig\Track\Middleware
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param mixed $response
     * @return ClosureMiddleware|Middleware
     */
    public function setResponse($response) : \Rig\Track\Middleware
    {
        $this->response = $response;
        return $this;
    }


    public function __invoke(...$args) : \Rig\Track\Middleware
    {
        $callable = $this->getCallable();
        $boundCallable = $callable->bindTo($this, $this);
        $boundCallable();
        return $this;
    }

    /**
     * @param mixed $callable
     * @return $this|Middleware
     */
    public function setCallable($callable) : \Rig\Track\Middleware
    {
        $this->callable = $callable;
        return $this;
    }
}
