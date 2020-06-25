<?php

namespace Application\Command\Exception;

class TimedOutCommandException extends \Exception
{
    private $timeout;

    /**
     * @param string $userFirendlyTimeout
     * @return $this
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