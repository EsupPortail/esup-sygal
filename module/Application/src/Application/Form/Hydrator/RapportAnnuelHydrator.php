<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\RapportAnnuel;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 * @property EntityManager $objectManager
 *
 * @author Unicaen
 */
class RapportAnnuelHydrator extends DoctrineObject
{
    /**
     * @param array $data
     * @param RapportAnnuel $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $data['estFinal'] = (bool) $data['estFinal'];

        return parent::hydrate($data, $object);
    }
}