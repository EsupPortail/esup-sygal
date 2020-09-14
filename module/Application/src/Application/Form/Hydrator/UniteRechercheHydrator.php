<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\UniteRecherche;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class UniteRechercheHydrator extends DoctrineObject
{
    /**
     * Extract values from an object
     *
     * @param  UniteRecherche $ur
     * @return array
     */
    public function extract($ur)
    {
        $data = parent::extract($ur);
        $data['libelle'] = $ur->getLibelle();
        $data['code'] = $ur->getStructure()->getCode();
        $data['sigle'] = $ur->getSigle();
        $data['cheminLogo'] = $ur->getCheminLogo();
        $data['RNSR'] = $ur->getRNSR();
        $data['estFerme'] = $ur->getStructure()->isFerme();
        $data['id_ref'] = $ur->getStructure()->getIdRef();

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  UniteRecherche $ur
     * @return UniteRecherche
     */
    public function hydrate(array $data, $ur)
    {
        /** @var UniteRecherche $object */
        $object = parent::hydrate($data, $ur);

        $object->setLibelle($data['libelle']);
        $object->getStructure()->setCode($data['code']);
        $object->setSigle($data['sigle']);
        $object->setCheminLogo($data['cheminLogo']);
        $object->setRNSR($data['RNSR']);
        $object->getStructure()->setIdRef($data['id_ref']);
        if (isset($data['estFerme']) AND $data['estFerme'] === "1") $object->getStructure()->setFerme(true); else $object->getStructure()->setFerme(false);

        return $object;
    }
}