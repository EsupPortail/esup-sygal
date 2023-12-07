<?php

namespace Substitution\Service\Doublon\Doctorant;

use Doctorant\Service\DoctorantService;
use Psr\Container\ContainerInterface;

class DoctorantDoublonServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantDoublonService
    {
        $service = new DoctorantDoublonService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}