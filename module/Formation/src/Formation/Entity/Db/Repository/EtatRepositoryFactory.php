<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Etat;
use Interop\Container\ContainerInterface;

class EtatRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return EtatRepository
     */
    public function __invoke(ContainerInterface $container) : EtatRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var EtatRepository $repository */
        $repository = $entitymanager->getRepository(Etat::class);
        return $repository;
    }
}