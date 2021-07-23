<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Presence;
use Interop\Container\ContainerInterface;

class PresenceRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return PresenceRepository
     */
    public function __invoke(ContainerInterface $container) : PresenceRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var PresenceRepository $repository */
        $repository = $entitymanager->getRepository(Presence::class);
        return $repository;
    }
}