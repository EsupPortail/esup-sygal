<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\ProfilHydrator;
use Application\Form\ProfilForm;
use Interop\Container\ContainerInterface;

class ProfilFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var ProfilHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ProfilHydrator::class);

        /** @var ProfilForm $form */
        $form = new ProfilForm();
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}