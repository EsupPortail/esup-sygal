<?php

namespace ComiteSuivi\Form\ComiteSuivi;



use Interop\Container\ContainerInterface;

class RefusFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RefusForm
     */
    public function __invoke(ContainerInterface $container)
    {
        $form = new RefusForm();
        return $form;
    }
}
