<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Seance;
use Interop\Container\ContainerInterface;

class SeanceRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceRepository
     */
    public function __invoke(ContainerInterface $container) : SeanceRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var SeanceRepository $repository */
        $repository = $entitymanager->getRepository(Seance::class);
        return $repository;
    }
}