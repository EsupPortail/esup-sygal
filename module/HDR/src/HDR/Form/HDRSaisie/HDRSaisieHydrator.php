<?php

namespace HDR\Form\HDRSaisie;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use HDR\Entity\Db\HDR;

class HDRSaisieHydrator  extends DoctrineObject
{
    /**
     * @param object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var HDR $object */
        $data = parent::extract($object);

        $data ['generalites'] = $object;
        $data ['structures'] = $object;
//        $data ['encadrements'] = $object;
        $data ['direction'] = $object;

        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @return HDR
     */
    public function hydrate(array $data, object $object): HDR
    {
        return parent::hydrate($data,$object);
    }
}