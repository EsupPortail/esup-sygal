<?php

namespace UnicaenIdref\View\Helper;

use Psr\Container\ContainerInterface;

class IdrefPopupTriggerViewHelperFactory
{
    public function __invoke(ContainerInterface $container): IdrefPopupTriggerViewHelper
    {
        return new IdrefPopupTriggerViewHelper();
    }
}