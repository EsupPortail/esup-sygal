<?php

namespace Individu\Hydrator;

use Application\Entity\Db\Pays;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class IndividuHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        $data = parent::extract($object);

        if (array_key_exists($key = 'paysNationalite', $data) && $data[$key] instanceof Pays) {
            $data[$key] = $data[$key]->getId();
        }

        return $data;
    }
}