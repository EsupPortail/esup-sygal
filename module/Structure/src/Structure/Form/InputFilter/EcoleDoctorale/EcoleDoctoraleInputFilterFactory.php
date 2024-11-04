<?php

namespace Structure\Form\InputFilter\EcoleDoctorale;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcoleDoctoraleInputFilterFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EcoleDoctoraleInputFilter
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        return new EcoleDoctoraleInputFilter($em);
    }
}