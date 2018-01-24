<?php

namespace Application\Provider;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Application identity provider factory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class IdentityProviderFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $user                   = $serviceLocator->get('zfcuser_user_service');
        $simpleIdentityProvider = new IdentityProvider($user->getAuthService());
        
        return $simpleIdentityProvider;
    }
}