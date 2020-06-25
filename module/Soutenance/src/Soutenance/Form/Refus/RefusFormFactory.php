<?php

namespace Soutenance\Form\Refus;

use Interop\Container\ContainerInterface;

class RefusFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RefusForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var RefusForm $form */
        $form = new RefusForm();

        return $form;
    }
}