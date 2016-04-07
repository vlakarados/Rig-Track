<?php

namespace Rig\Track;

class Handler
{
    protected $params;
    protected $beforeCallable = array();
    protected $afterCallable = array();

    public function __construct($callable, $params, $beforeCallable, $afterCallable)
    {
        $this->callable = $callable;
        $this->params = $params;
        $this->beforeCallable = $beforeCallable;
        $this->afterCallable = $afterCallable;

    }
}
