<?php

namespace Admission\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;

/**
 * @author Unicaen
 */
class InscriptionHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        return parent::extract($object); // TODO: Change the autogenerated stub
    }

    public function hydrate(array $data, object $object): object
    {
        return parent::hydrate($data, $object); // TODO: Change the autogenerated stub
    }

}