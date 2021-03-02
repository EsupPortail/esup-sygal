<?php

namespace Application\View\Helper;

use Interop\Container\ContainerInterface;
use Zend\Http\Request;

class SortableHelperFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var Request $request */
        $request = $container->get('request');

        $helper = new Sortable();
        $helper->setRequest($request);

        return $helper;
    }
}