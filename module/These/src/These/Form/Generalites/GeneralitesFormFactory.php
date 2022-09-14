<?php

namespace These\Form\Generalites;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;

class GeneralitesFormFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GeneralitesForm
    {
        $form = new GeneralitesForm('Generalites');

        /** @var \Doctrine\Laminas\Hydrator\DoctrineObject $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);
        $form->setHydrator($hydrator);

        return $form;
    }
}