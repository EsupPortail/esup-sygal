<?php

namespace Admission\Form\Admission;

use Admission\Entity\Db\Admission;
use Admission\Hydrator\AdmissionHydrator;
use Admission\Hydrator\IndividuHydrator;
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
        /** @var IndividuHydrator $IndividuHydrator */
        $admissionHydrator = $container->get('HydratorManager')->get(AdmissionHydrator::class);

        $form = new AdmissionForm();
        $form->setHydrator($admissionHydrator);
        $form->setObject(new Admission());

        return $form;
    }
}