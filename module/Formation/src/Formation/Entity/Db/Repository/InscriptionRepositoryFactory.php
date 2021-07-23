<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Inscription;
use Interop\Container\ContainerInterface;

class InscriptionRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionRepository
     */
    public function __invoke(ContainerInterface $container) : InscriptionRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var InscriptionRepository $repository */
        $repository = $entitymanager->getRepository(Inscription::class);
        return $repository;
    }
}