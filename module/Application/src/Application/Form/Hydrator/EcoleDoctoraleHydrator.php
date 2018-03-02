<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\EcoleDoctorale;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class EcoleDoctoraleHydrator extends DoctrineObject
{
    /**
     * Extract values from an object
     *
     * @param  EcoleDoctorale $ed
     * @return array
     */
    public function extract($ed)
    {
        $data = parent::extract($ed);
        $data['libelle'] = $ed->getLibelle();
        $data['sigle'] = $ed->getSigle();
        $data['cheminLogo'] = $ed->getCheminLogo();

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  EcoleDoctorale $ed
     * @return EcoleDoctorale
     */
    public function hydrate(array $data, $ed)
    {
        /** @var EcoleDoctorale $object */
        $object = parent::hydrate($data, $ed);

        $object->setLibelle($data['libelle']);
        $object->setSigle($data['sigle']);
        $object->setCheminLogo($data['cheminLogo']);

        return $object;
    }
}