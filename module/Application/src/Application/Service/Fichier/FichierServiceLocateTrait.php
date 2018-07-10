<?php

namespace Application\Service\Fichier;

use Zend\ServiceManager\ServiceLocatorInterface;

trait FichierServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return FichierService
     */
    public function locateFichierService(ServiceLocatorInterface $sl)
    {
        /** @var FichierService $service */
        $service = $sl->get('FichierService');

        return $service;
    }
}