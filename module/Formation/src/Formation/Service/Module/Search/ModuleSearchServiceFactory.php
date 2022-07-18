<?php

namespace Formation\Service\Module\Search;

use Formation\Entity\Db\Module;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ModuleSearchServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ModuleSearchService
    {
        $service = new ModuleSearchService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        /** @var \Formation\Entity\Db\Repository\ModuleRepository $repository */
        $repository = $em->getRepository(Module::class);
        $service->setModuleRepository($repository);

        return $service;
    }
}