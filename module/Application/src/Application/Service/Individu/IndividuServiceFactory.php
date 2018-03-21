<?php

namespace Application\Service\Individu;

use Zend\ServiceManager\ServiceLocatorInterface;

class IndividuServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        $service = new IndividuService();

        return $service;
    }
}