<?php

namespace Formation\Form\Module;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ModuleFormFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ModuleForm
    {
        /** @var ModuleHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ModuleHydrator::class);

        $form = new ModuleForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}