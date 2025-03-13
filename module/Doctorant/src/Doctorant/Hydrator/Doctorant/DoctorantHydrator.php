<?php

namespace Doctorant\Hydrator\Doctorant;

use Doctrine\Laminas\Hydrator\DoctrineObject;

class DoctorantHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        /** @var \Doctorant\Entity\Db\Doctorant $object */

        $data = parent::extract($object);

        $data['idRef'] = $object->getIndividu()->getIdRef();

        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        /** @var \Doctorant\Entity\Db\Doctorant $object */

        $object->getindividu()->setIdRef($data['idRef'] ?? null);

        return parent::hydrate($data, $object);
    }
}