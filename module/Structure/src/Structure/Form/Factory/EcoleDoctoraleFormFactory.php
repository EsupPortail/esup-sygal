<?php

namespace Structure\Form\Factory;

use Interop\Container\ContainerInterface;
use Structure\Form\EcoleDoctoraleForm;
use Structure\Form\Hydrator\EcoleDoctoraleHydrator;
use Structure\Form\InputFilter\EcoleDoctorale\EcoleDoctoraleInputFilter;

class EcoleDoctoraleFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EcoleDoctoraleForm
    {
        $form = new EcoleDoctoraleForm();

        /** @var EcoleDoctoraleHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('EcoleDoctoraleHydrator');
        $form->setHydrator($hydrator);

        /** @var EcoleDoctoraleInputFilter $inputFilter */
        $inputFilter = $container->get('InputFilterManager')->get(EcoleDoctoraleInputFilter::class);
        $form->setInputFilter($inputFilter);

        return $form;
    }
}