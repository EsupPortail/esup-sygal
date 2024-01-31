<?php

namespace Admission\Hydrator\Financement;

use Admission\Entity\Db\Financement;
use Application\Entity\Db\OrigineFinancement;
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

        if (array_key_exists($key = 'financement', $data) && $data[$key] instanceof OrigineFinancement) {
            $data["financement"] = $data["financement"]->getId();
        }

        $data['verificationFinancement'] = $object->getVerificationFinancement()->first() ?: null;

        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        //Si la case contratDoctoral est décochée, on met à null les valeurs des champs reliés
        if (isset($data["contratDoctoral"]) && !$data["contratDoctoral"]) {
            $data["financement"] = null;
        }

        $data["financement"] = !empty($data["financement"]) ? $data["financement"] : null;

        if (isset($data['verificationFinancement']) && !is_array($data['verificationFinancement'])) {
            $data['verificationFinancement'] = [$data['verificationFinancement']];
        }

        return parent::hydrate($data, $object);
    }

}