<?php

namespace Structure\Form\Hydrator;

use Structure\Entity\Db\UniteRecherche;

class UniteRechercheHydrator extends StructureHydrator
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
        /** @var UniteRecherche $object */
        $object = parent::hydrate($data, $ur);

        $object->setRNSR($data['RNSR']);

        return $object;
    }
}