<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use Interop\Container\ContainerInterface;

class SessionRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionRepository
     */
    public function __invoke(ContainerInterface $container) : SessionRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var SessionRepository $repository */
        $repository = $entitymanager->getRepository(Session::class);
        return $repository;
    }
}