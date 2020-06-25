<?php

namespace Soutenance\Form\LabelEuropeen;

use Interop\Container\ContainerInterface;

class LabelEuropeenFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var LabelEuropeenHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(LabelEuropeenHydrator::class);

        /** @var LabelEuropeenForm $form */
        $form = new LabelEuropeenForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}