<?php

namespace Application\Service\Individu;

use Zend\ServiceManager\ServiceLocatorInterface;

trait IndividuServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return IndividuService
     */
    public function locateIndividuService(ServiceLocatorInterface $sl)
    {
        /** @var IndividuService $service */
        $service = $sl->get('IndividuService');

        return $service;
    }
}