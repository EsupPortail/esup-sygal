<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use FOrmation\Entity\Db\EnqueteReponse;
use Interop\Container\ContainerInterface;

class EnqueteReponseRepositoryFactory {
    /**
     * @param ContainerInterface $container
     * @return EnqueteReponseRepository
     */
    public function __invoke(ContainerInterface $container) : EnqueteReponseRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var EnqueteReponseRepository $repository */
        $repository = $entitymanager->getRepository(EnqueteReponse::class);
        return $repository;
    }
}