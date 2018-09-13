<?php

namespace Application\Assertion;

use Application\Assertion\Interfaces\ControllerAssertionInterface;

abstract class ControllerAssertion implements ControllerAssertionInterface
{
    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        if (array_key_exists('controller', $context)) {
            $this->controller = $context['controller'];
        }
        if (array_key_exists('action', $context)) {
            $this->action = $context['action'];
        }
    }
}