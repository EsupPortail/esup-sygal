<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Formateur;
use Interop\Container\ContainerInterface;

class FormateurRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return FormateurRepository
     */
    public function __invoke(ContainerInterface $container) : FormateurRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var FormateurRepository $repository */
        $repository = $entitymanager->getRepository(Formateur::class);
        return $repository;
    }
}