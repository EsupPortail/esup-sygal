<?php

namespace Doctorant\Form\MissionEnseignement;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MissionEnseignementFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return MissionEnseignementForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : MissionEnseignementForm
    {
        /** @var MissionEnseignementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(MissionEnseignementHydrator::class);

        $form = new MissionEnseignementForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}