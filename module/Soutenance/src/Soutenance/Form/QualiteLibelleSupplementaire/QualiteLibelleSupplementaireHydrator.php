<?php

namespace Soutenance\Form\QualiteLibelleSupplementaire;

use Soutenance\Entity\QualiteLibelleSupplementaire;
use Laminas\Hydrator\HydratorInterface;

class QualiteLibelleSupplementaireHydrator implements HydratorInterface {

    /**
     * @param QualiteLibelleSupplementaire $object
     * @return array
     */
    public function extract($object): array
    {
        $data = [
            'qualite' => $object->getQualite()->getLibelle(),
            'libelle' => $object->getLibelle(),
        ];
        return $data;
    }

    /**
     * @param QualiteLibelleSupplementaire $object
     * @param array $data
     * @return QualiteLibelleSupplementaire
     */
    public function hydrate(array $data, $object)
    {
        $object->setLibelle($data['libelle']);
        return $object;
    }
}