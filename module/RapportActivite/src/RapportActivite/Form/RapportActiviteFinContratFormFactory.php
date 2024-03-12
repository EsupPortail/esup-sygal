<?php

namespace RapportActivite\Form;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\Strategy\BooleanStrategy;
use Psr\Container\ContainerInterface;

class RapportActiviteFinContratFormFactory
{
    public function __invoke(ContainerInterface $container): RapportActiviteFinContratForm
    {
        $form = new RapportActiviteFinContratForm('rapport-activite');

        $hydrator = new ClassMethodsHydrator(false);
        $hydrator->addStrategy('estFinContrat', new BooleanStrategy('1', '0'));
        $form->setHydrator($hydrator);

        return $form;
    }
}