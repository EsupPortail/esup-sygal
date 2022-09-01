<?php

namespace RapportActivite\Hydrator;

use RapportActivite\Entity\Db\RapportActivite;
use Doctrine\ORM\EntityManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;

/**
 * @property EntityManager $objectManager
 *
 * @author Unicaen
 */
class RapportActiviteHydrator extends DoctrineObject
{
    /**
     * @param array $data
     * @param RapportActivite $object
     * @return object
     */
    public function hydrate(array $data, $object): object
    {
        $data['estFinal'] = (bool) $data['estFinal'];

        return parent::hydrate($data, $object);
    }
}