<?php

namespace Structure\Form\Hydrator;

use Structure\Entity\Db\UniteRecherche;

class UniteRechercheHydrator extends StructureConcreteHydrator
{
    /**
     * @param UniteRecherche $ur
     * @return array
     */
    public function extract($ur): array
    {
        $data = parent::extract($ur);

        $data['RNSR'] = $ur->getRNSR();

        return $data;
    }

    /**
     * @param array $data
     * @param UniteRecherche $ur
     * @return UniteRecherche
     */
    public function hydrate(array $data, $ur): UniteRecherche
    {
        if (!isset($data['id']) || $data['id'] === "") $data['id'] = null;

        /** @var UniteRecherche $object */
        $object = parent::hydrate($data, $ur);

        $object->setRNSR($data['RNSR']);

        return $object;
    }
}