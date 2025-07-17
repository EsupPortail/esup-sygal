<?php

namespace Doctorant\Hydrator\Doctorant;

use Psr\Container\ContainerInterface;

class DoctorantHydratorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantHydrator
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        return new DoctorantHydrator($em);
    }
}