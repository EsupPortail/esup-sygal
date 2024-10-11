<?php

namespace Admission\Form\Transmission;

use Admission\Entity\Db\Transmission;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TransmissionFormFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TransmissionForm
    {
        $transmissionHydrator = $container->get('HydratorManager')->get(DoctrineObject::class);
        $form = new TransmissionForm();
        $form->setHydrator($transmissionHydrator);
        $form->setObject(new Transmission());

        return $form;
    }
}