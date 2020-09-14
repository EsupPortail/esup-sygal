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
        $data['code'] = $ed->getStructure()->getCode();
        $data['sigle'] = $ed->getSigle();
        $data['cheminLogo'] = $ed->getCheminLogo();
        $data['estFerme'] = $ed->getStructure()->isFerme();
        $data['id_ref'] = $ed->getStructure()->getIdRef();

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
        $object->getStructure()->setCode($data['code']);
        $object->setSigle($data['sigle']);
        $object->setCheminLogo($data['cheminLogo']);
        $object->getStructure()->setIdRef($data['id_ref']);
        if (isset($data['estFerme']) AND $data['estFerme'] === "1") $object->getStructure()->setFerme(true); else $object->getStructure()->setFerme(false);

        return $object;
    }
}