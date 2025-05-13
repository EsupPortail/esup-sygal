<?php

namespace Fichier\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;

class FichierHydrator extends DoctrineObject
{
    public function hydrate(array $data, object $object): object
    {
        // pour l'instant, interdit de modifier autre chose que l'id permanent
        $data = [
            'idPermanent' => $data['idPermanent'] ?? null,
        ];

        return parent::hydrate($data, $object);
    }

}