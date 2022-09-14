<?php

namespace These\Form\Confidentialite;

use Interop\Container\ContainerInterface;

class ConfidentialiteFormFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConfidentialiteForm
    {
        $form = new ConfidentialiteForm('Confidentialite');

        return $form;
    }
}