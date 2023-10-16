<?php

namespace Admission\Form\Admission;

use Admission\Entity\Db\Admission;
use Admission\Hydrator\AdmissionHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionFormFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionForm
    {
        /** @var AdmissionHydrator $admissionHydrator */
        $admissionHydrator = $container->get('HydratorManager')->get(AdmissionHydrator::class);
//        $admissionHydrator = $container->get('doctrine.entitymanager.orm_default')->get(AdmissionHydrator::class);
        $form = new AdmissionForm();
        $form->setHydrator($admissionHydrator);
        $form->setObject(new Admission());

        return $form;
    }
}