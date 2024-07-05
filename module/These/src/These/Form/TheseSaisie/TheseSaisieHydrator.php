<?php

namespace These\Form\TheseSaisie;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use These\Entity\Db\These;

class TheseSaisieHydrator  extends DoctrineObject
{
    /**
     * @param object $object
     * @return array
     */
    public function extract(object $object): array
    {
        /** @var These $object */
        $data = parent::extract($object);

        $data ['generalites'] = $object;
        $data ['structures'] = $object;
        $data ['encadrements'] = $object;
        $data ['direction'] = $object;

        return $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @return These
     */
    public function hydrate(array $data, object $object): These
    {
        return parent::hydrate($data,$object);
    }
}