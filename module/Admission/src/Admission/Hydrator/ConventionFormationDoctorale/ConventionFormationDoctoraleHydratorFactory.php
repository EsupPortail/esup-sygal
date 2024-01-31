<?php

namespace Admission\Hydrator\ConventionFormationDoctorale;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ConventionFormationDoctoraleHydratorFactory implements FactoryInterface
{
    /**
     * Create hydrator
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return ConventionFormationDoctoraleHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');

        return new ConventionFormationDoctoraleHydrator($entityManager);
    }
}