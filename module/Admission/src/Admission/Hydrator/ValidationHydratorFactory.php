<?php

namespace Admission\Hydrator;

use Application\Application\Form\Hydrator\RecrutementHydrator;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ValidationHydratorFactory implements FactoryInterface
{
    /**
     * Create hydrator
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ValidationHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');

        return new ValidationHydrator($entityManager);
    }
}