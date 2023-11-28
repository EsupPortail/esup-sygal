<?php

namespace Admission\Hydrator\Financement;

use Admission\Entity\Db\Financement;
use Doctrine\Laminas\Hydrator\DoctrineObject;

/**
 * @author Unicaen
 */
class FinancementHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        /** @var Financement $object */
        $data = parent::extract($object);

        $data['verificationFinancement'] = $object->getVerificationFinancement()->first() ?: null;

        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        //Si la case contratDoctoral est décochée, on met à null les valeurs des champs reliés
        if (isset($data["contratDoctoral"]) && !$data["contratDoctoral"]) {
            $data["employeurContrat"] = null;
        }

        if (isset($data['verificationFinancement']) && !is_array($data['verificationFinancement'])) {
            $data['verificationFinancement'] = [$data['verificationFinancement']];
        }

        return parent::hydrate($data, $object);
    }

}