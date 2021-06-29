<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Module;
use Interop\Container\ContainerInterface;
use Module\Entity\Db\Repository\ModuleRepository;

class ModuleRepositoryFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleRepository
     */
    public function __invoke(ContainerInterface $container) : ModuleRepository
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        /** @var ModuleRepository $repository */
        $repository = $entitymanager->getRepository(Module::class);
        return $repository;
    }
}