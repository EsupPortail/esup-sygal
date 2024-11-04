<?php

namespace Structure\Form\InputFilter\UniteRecherche;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UniteRechercheInputFilterFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): UniteRechercheInputFilter
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        return new UniteRechercheInputFilter($em);
    }
}