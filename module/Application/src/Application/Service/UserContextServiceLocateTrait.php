<?php

namespace Application\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

trait UserContextServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return UserContextService
     */
    public function locateUserContextService(ServiceLocatorInterface $sl)
    {
        /** @var UserContextService $service */
        $service = $sl->get('UserContextService');

        return $service;
    }
}