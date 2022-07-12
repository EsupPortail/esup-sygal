<?php

namespace Formation\Service\Inscription\Search;

use Formation\Entity\Db\Inscription;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InscriptionSearchServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InscriptionSearchService
    {
        $service = new InscriptionSearchService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        /** @var \Formation\Entity\Db\Repository\InscriptionRepository $repository */
        $repository = $em->getRepository(Inscription::class);
        $service->setInscriptionRepository($repository);

        return $service;
    }
}