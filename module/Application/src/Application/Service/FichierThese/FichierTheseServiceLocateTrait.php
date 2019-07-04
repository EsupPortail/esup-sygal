<?php

namespace Application\Service\FichierThese;

use Zend\ServiceManager\ServiceLocatorInterface;

trait FichierTheseServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return FichierTheseService
     */
    public function locateFichierTheseService(ServiceLocatorInterface $sl)
    {
        /** @var FichierTheseService $service */
        $service = $sl->get('FichierTheseService');

        return $service;
    }
}