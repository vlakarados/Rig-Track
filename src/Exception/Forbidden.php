<?php

namespace Rig\Track\Exception;

class Forbidden extends \Exception
{
    public $httpCode = 403;
    public function __construct($method, $uri)
    {
        $this->message = 'Forbidden "'.$method.'" / "'.$uri.'"';
    }
}
