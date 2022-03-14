<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Rapport;
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
     * @param Rapport $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $data['estFinal'] = (bool) $data['estFinal'];

        return parent::hydrate($data, $object);
    }
}