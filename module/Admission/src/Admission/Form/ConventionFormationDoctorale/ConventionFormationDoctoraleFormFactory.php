<?php

namespace Admission\Form\ConventionFormationDoctorale;

use Admission\Entity\Db\ConventionFormationDoctorale;
use Admission\Hydrator\ConventionFormationDoctorale\ConventionFormationDoctoraleHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ConventionFormationDoctoraleFormFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConventionFormationDoctoraleForm
    {
        /** @var ConventionFormationDoctoraleHydrator $conventionFormationDoctoraleHydrator */
        $conventionFormationDoctoraleHydrator = $container->get('HydratorManager')->get(ConventionFormationDoctoraleHydrator::class);
        $form = new ConventionFormationDoctoraleForm();
        $form->setHydrator($conventionFormationDoctoraleHydrator);
        $form->setObject(new ConventionFormationDoctorale());

        return $form;
    }
}