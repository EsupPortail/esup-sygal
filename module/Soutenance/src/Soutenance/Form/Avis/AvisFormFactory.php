<?php

namespace Soutenance\Form\Avis;

use Interop\Container\ContainerInterface;
use Laminas\Form\FormElementManager;

class AvisFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return AvisForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var AvisHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(AvisHydrator::class);

        /** @var AvisForm $form */
        $form = new AvisForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}