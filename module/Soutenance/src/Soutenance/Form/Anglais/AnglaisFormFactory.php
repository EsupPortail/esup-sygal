<?php

namespace Soutenance\Form\Anglais;

use Interop\Container\ContainerInterface;

class AnglaisFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return AnglaisForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var AnglaisHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(AnglaisHydrator::class);

        /** @var AnglaisForm $form */
        $form = new AnglaisForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}