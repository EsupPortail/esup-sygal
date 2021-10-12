<?php

namespace Application\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class UserContextServiceAwareInitializer implements InitializerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof UserContextServiceAwareInterface) {
            $instance->setUserContextService($container->get('UnicaenAuth\Service\UserContext'));
        }

        return $instance;
    }
}