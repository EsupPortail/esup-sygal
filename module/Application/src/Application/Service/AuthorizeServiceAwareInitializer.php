<?php

namespace Application\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

class AuthorizeServiceAwareInitializer implements InitializerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof AuthorizeServiceAwareInterface) {
            $instance->setAuthorizeService($container->get('BjyAuthorize\Service\Authorize'));
        }

        return $instance;
    }
}