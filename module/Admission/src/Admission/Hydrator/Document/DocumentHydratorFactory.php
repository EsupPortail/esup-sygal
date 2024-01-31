<?php

namespace Admission\Hydrator\Document;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DocumentHydratorFactory implements FactoryInterface
{
    /**
     * Create hydrator
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return DocumentHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');

        return new DocumentHydrator($entityManager);
    }
}