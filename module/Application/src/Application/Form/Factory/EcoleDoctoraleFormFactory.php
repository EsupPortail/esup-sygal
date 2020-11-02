<?php

namespace Application\Form\Factory;

use Application\Form\EcoleDoctoraleForm;
use Application\Form\Hydrator\EcoleDoctoraleHydrator;
use Interop\Container\ContainerInterface;

class EcoleDoctoraleFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EcoleDoctoraleHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('EcoleDoctoraleHydrator');

        $form = new EcoleDoctoraleForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}