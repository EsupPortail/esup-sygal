<?php

namespace Application\Service\Etablissement;

use Zend\ServiceManager\ServiceLocatorInterface;

trait EtablissementServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return EtablissementService
     */
    public function locateEtablissementService(ServiceLocatorInterface $sl)
    {
        /** @var EtablissementService $service */
        $service = $sl->get('EtablissementService');

        return $service;
    }
}