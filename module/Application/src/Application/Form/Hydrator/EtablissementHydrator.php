<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Etablissement;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class EtablissementHydrator extends DoctrineObject
{
    /**
     * Extract values from an object
     *
     * @param  Etablissement $etablissement
     * @return array
     */
    public function extract($etablissement)
    {
        $data = parent::extract($etablissement);
        $data['libelle'] = $etablissement->getLibelle();
        $data['cheminLogo'] = $etablissement->getCheminLogo();

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  Etablissement $etablissement
     * @return Etablissement
     */
    public function hydrate(array $data, $etablissement)
    {
        /** @var Etablissement $object */
        $object = parent::hydrate($data, $etablissement);

        $object->setLibelle($data['libelle']);
        $object->setCheminLogo($data['cheminLogo']);

        return $object;
    }
}