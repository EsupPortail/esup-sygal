<?php

namespace Soutenance\Form\Justificatif;

use Interop\Container\ContainerInterface;

class JustificatifFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): JustificatifForm
    {
        /** @var JusticatifHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(JusticatifHydrator::class);

        $form = new JustificatifForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}