<?php

namespace Rig\Track;

interface Middleware extends \Rig\HTTP\HTTPAware
{
    public function __invoke(...$args) : \Rig\Track\Middleware;
    public function setCallable($callable) : \Rig\Track\Middleware;
}
