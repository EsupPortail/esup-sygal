<?php

namespace Soutenance\Form\DateLieu;

use Interop\Container\ContainerInterface;

class DateLieuFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DateLieuHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DateLieuHydrator::class);

        /** @var DateLieuForm $form */
        $form = new DateLieuForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}