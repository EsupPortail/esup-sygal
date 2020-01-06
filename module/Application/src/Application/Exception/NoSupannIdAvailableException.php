<?php

namespace Application\Exception;

use Throwable;

class NoSupannIdAvailableException extends \Exception
{
    public function __construct($message = "Le Supann Id est null", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}