<?php

namespace Fichier\Form;

use Fichier\Hydrator\FichierHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class FichierFormFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FichierForm
    {
        /** @var FichierHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(FichierHydrator::class);

        $form = new FichierForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}