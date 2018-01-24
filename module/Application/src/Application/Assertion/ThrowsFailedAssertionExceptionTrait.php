<?php

namespace Application\Assertion;

use Application\Assertion\Exception\FailedAssertionException;

trait ThrowsFailedAssertionExceptionTrait
{
    /**
     * @param boolean $condition Expression logique qui doit être vraie
     * @param string  $message Message de l'exception lancée si l'expression logique est fausse
     * @return static
     * @throws FailedAssertionException Si l'expression logique est fausse
     */
    protected function assertTrue($condition, $message = "")
    {
        if (! $condition) {
            throw new FailedAssertionException($message);
        }

        return $this;
    }

    /**
     * @param boolean $condition Expression logique qui doit être fausse
     * @param string  $message Message de l'exception lancée si l'expression logique est vraie
     * @return static
     * @throws FailedAssertionException Si l'expression logique est vraie
     */
    protected function assertFalse($condition, $message = "")
    {
        if ($condition) {
            throw new FailedAssertionException($message);
        }

        return $this;
    }
}