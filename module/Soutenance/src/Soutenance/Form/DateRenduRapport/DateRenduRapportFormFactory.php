<?php

namespace Soutenance\Form\DateRenduRapport;

use Interop\Container\ContainerInterface;

class DateRenduRapportFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return DateRenduRapportForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var DateRenduRapportHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DateRenduRapportHydrator::class);

        /** @var DateRenduRapportForm $form */
        $form = new DateRenduRapportForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}