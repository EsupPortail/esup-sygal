<?php

namespace Doctorant\Form\MissionEnseignement;

use Psr\Container\ContainerInterface;

class MissionEnseignementHydratorFactory {

    public function __invoke(ContainerInterface $container) : MissionEnseignementHydrator
    {
        return new MissionEnseignementHydrator();
    }
}