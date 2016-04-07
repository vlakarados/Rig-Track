<?php

namespace Rig\Track;

class Dispatcher
{
    protected $injector;
    protected $middleware;
    protected $request;
    protected $response;
    protected $router;

    protected $closureController = '\Rig\Control\ClosureController';

    public function __construct(
        \Rig\Track\Middleware $middleware,
        \Auryn\Injector $injector,
        \Rig\HTTP\Request $request,
        \Rig\HTTP\Response $response,
        \Rig\Track\Router $router
    ) {
        $this->middleware = $middleware;
        $this->injector = $injector;
        $this->request = $request;
        $this->response = $response;
        $this->router = $router;
    }

    public function executeBefore($beforeCallables)
    {
        $middleware = $this->middleware;
        $results = [];
        foreach ($beforeCallables as $before) {
            $middleware->setCallable($before);
            $results[] = $middleware();
        }
        return $results;
    }

    public function executeAfter($afterCallables)
    {
        $middleware = $this->middleware;
        $results = [];
        foreach ($afterCallables as $after) {
            $middleware->setCallable($after);
            $results[] = $middleware();
        }
        return $results;
    }

    public function execute(\Rig\Track\Route $route)
    {
        $callable = $route->getCallable();

        if (is_closure($callable)) {
            $subject = $this->injector->make($this->closureController);
        } elseif (is_array($callable)) {
            $subject = $this->injector->make($callable[0]);
        } else {
            throw new \InvalidArgumentException('Route handler is not a callable instance');
        }


        // Provision the controller with our stuff
        $subject->setParams($route->getParams());
        $subject->setRouter($this->router);
        $subject->setRequest($this->request);
        $subject->setResponse($this->response);


        // Prepare route pattern arguments so Auryn does not resolve them)
        $actionArguments = array();
        foreach ($route->getParams() as $key => $value) {
            $actionArguments[sprintf(':%s', $key)] = $value;
        }

        // Execute the action

        if (is_closure($callable)) {
            // call closure on behalf of the subject
            $boundCallable = $callable->bindTo($subject, $subject);
            $result = $this->injector->execute($boundCallable, $actionArguments);
        } elseif (is_array($callable)) {
            $result = $this->injector->execute(array($subject, $callable[1]), $actionArguments);
        }

        return $result;
    }
}
