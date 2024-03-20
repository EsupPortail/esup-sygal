<?php

namespace Depot\Form\Metadonnees;

use Interop\Container\ContainerInterface;
use These\Entity\Db\These;

class MetadonneeTheseFieldsetFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var MetadonneesTheseHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(MetadonneesTheseHydrator::class);
        $fieldset = new MetadonneeTheseFieldset();
        $fieldset->setObject(new These());

        $fieldset->setHydrator($hydrator);
        return $fieldset;
    }
}