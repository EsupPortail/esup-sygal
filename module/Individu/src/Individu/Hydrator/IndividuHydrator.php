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

    public function hydrate(array $data, object $object): object
    {
        /** @var \Individu\Entity\Db\Individu $object */

        if (!array_key_exists('sourceCode', $data)) {
            $data['sourceCode'] = uniqid('', true);
        }

        return parent::hydrate($data, $object);
    }
}