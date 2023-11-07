<?php

namespace Admission\Hydrator;

use Admission\Entity\Db\Admission;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class AdmissionHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        /** @var Admission $object */
        $data = parent::extract($object);

        $data['inscription'] = $object->getInscription()->first() ?: null;
        $data['financement'] = $object->getFinancement()->first() ?: null;
        $data['etudiant'] = $object->getEtudiant()->first() ?: null;
        $data['validation'] = $object->getValidation()->first() ?: null;
        $data['document'] = $object->getDocument()->first() ?: null;

        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        if (isset($data['etudiant']) && !is_array($data['etudiant'])) {
            $data['etudiant'] = [$data['etudiant']];
        }
        if (isset($data['inscription']) && !is_array($data['inscription'])) {
            $data['inscription'] = [$data['inscription']];
        }
        if (isset($data['financement']) && !is_array($data['financement'])) {
            $data['financement'] = [$data['financement']];
        }
        if (isset($data['validation']) && !is_array($data['validation'])) {
            $data['validation'] = [$data['validation']];
        }
        return parent::hydrate($data, $object);
    }

}