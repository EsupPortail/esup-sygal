<?php

namespace Import\Service\Factory;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\ORM\EntityManager;
use Import\Service\CallService;
use Import\Service\DbService;
use Import\Service\FetcherService;
use UnicaenApp\Exception\LogicException;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class FetcherServiceFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        /** @var CallService $callService */
        $callService = $container->get(CallService::class);
        /** @var DbService $dbService */
        $dbService = $container->get(DbService::class);

        try {
            $config = $this->getConfig($container);
        } catch (AssertionFailedException $e) {
            throw new LogicException("La config du FetcherService est incorrecte.", null, $e);
        }

        $service = new FetcherService($config);
        $service->setCallService($callService);
        $service->setDbService($dbService);

        return $service;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws \Assert\AssertionFailedException
     */
    private function getConfig(ContainerInterface $container)
    {
        $config = $container->get('config');

        Assertion::keyIsset($config, 'import-api');
        Assertion::keyIsset($config['import-api'], 'etablissements');

        return $config['import-api']['etablissements'];
    }
}