<?php

namespace UnicaenAvis\Form;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Hydrator\AvisHydrator;

class AvisFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisForm
    {
        /** @var \UnicaenAvis\Hydrator\AvisHydrator $avisHydrator */
        $avisHydrator = $container->get('HydratorManager')->get(AvisHydrator::class);

        $form = new AvisForm();
        $form->setHydrator($avisHydrator);

        return $form;
    }
}