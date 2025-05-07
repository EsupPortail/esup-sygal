<?php

namespace ApplicationUnitTest\Controller;

use ApplicationUnitTest\Test\Provider\EntityProvider;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

abstract class AbstractControllerTestCase extends \UnicaenTest\Controller\AbstractControllerTestCase
{
    protected $traceError = false;

    private ?EntityProvider $ep = null;

    public function ep(): EntityProvider
    {
        if (null === $this->ep) {
            try {
                $config = $this->getApplicationServiceLocator()->get('Config');
            } catch (ContainerExceptionInterface $e) {
                throw new RuntimeException("Impossible d'obtenir la config de l'appli !", null, $e);
            }
            $this->ep = new EntityProvider($this->em(), $config['unicaen-test']);
        }

        return $this->ep;
    }
}