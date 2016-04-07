<?php

namespace Rig\Track;

interface RoutingEntity
{
    public function before($beforeCallable);
    public function after($afterCallable);
    public function setPrefix($prefix);
}
