<?php

namespace RapportActivite\Hydrator;

use Laminas\Hydrator\ClassMethodsHydrator;
use RapportActivite\Entity\Formation;

/**
 * @author Unicaen
 */
class FormationHydrator extends ClassMethodsHydrator
{
    /**
     * @param array $data
     * @param \RapportActivite\Entity\Formation $object
     * @return \RapportActivite\Entity\Formation
     */
    public function hydrate(array $data, $object): Formation
    {
        $data['temps'] = (int) $data['temps'];

        parent::hydrate($data, $object);

        return $object;
    }
}