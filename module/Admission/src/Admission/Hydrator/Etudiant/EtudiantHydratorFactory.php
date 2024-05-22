<?php

namespace Admission\Hydrator\Etudiant;

use Application\Service\Pays\PaysService;
use Doctrine\ORM\EntityManager;
use Individu\Service\IndividuService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EtudiantHydratorFactory implements FactoryInterface
{
    /**
     * Create hydrator
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return EtudiantHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        $paysService = $container->get(PaysService::class);

        $etudiantHydrator = new EtudiantHydrator($entityManager);
        $etudiantHydrator->setPaysService($paysService);
        return $etudiantHydrator;
    }
}