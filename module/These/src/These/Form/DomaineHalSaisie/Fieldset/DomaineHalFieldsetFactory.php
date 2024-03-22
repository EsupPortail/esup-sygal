<?php

namespace These\Form\DomaineHalSaisie\Fieldset;

use Interop\Container\ContainerInterface;
use These\Entity\Db\These;

class DomaineHalFieldsetFactory
{

    public function __invoke(ContainerInterface $container): DomaineHalFieldset
    {

        /** @var DomaineHalHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DomaineHalHydrator::class);
        $fieldset = new DomaineHalFieldset();
        $fieldset->setObject(new These());
        $fieldset->setHydrator($hydrator);
        return $fieldset;
    }
}