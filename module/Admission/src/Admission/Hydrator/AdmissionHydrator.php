<?php

namespace Admission\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;

/**
 * @author Unicaen
 */
class AdmissionHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        $data = parent::extract($object);
        $data['inscription'] = $object->getInscriptions()[0];
        $data['financement'] = $object->getFinancements()[0];
        $data['individu'] = $object->getIndividus()[0];
        $data['validation'] = $object->getValidations()[0];

        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        if (isset($data['inscription'])) {
            $data['inscriptions'][] = $data['inscription'];
            unset($data['inscription']);
        }

        if (isset($data['financement'])) {
            $data['financements'][] = $data['financement'];
            unset($data['financement']);
        }

        if (isset($data['individu'])) {
            $data['individus'][] = $data['individu'];
            unset($data['individu']);
        }

        if (isset($data['validation'])) {
            $data['validations'][] = $data['validation'];
            unset($data['validation']);
        }

        return parent::hydrate($data, $object);
    }

}