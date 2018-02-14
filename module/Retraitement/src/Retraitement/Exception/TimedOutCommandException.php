<?php

namespace Retraitement\Exception;

use UnicaenApp\Exception\RuntimeException;

class TimedOutCommandException extends RuntimeException
{
    private $timeout;

    /**
     * @param string $userFirendlyTimeout
     * @return TimedOutCommandException
     */
    public function setTimeout($userFirendlyTimeout)
    {
        $this->timeout = $userFirendlyTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}