<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Formation;
use Interop\Container\ContainerInterface;

class FormationRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationRepository
     */
    public function __invoke(ContainerInterface $container) : FormationRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var FormationRepository $repository */
        $repository = $entitymanager->getRepository(Formation::class);
        return $repository;
    }
}