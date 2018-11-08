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
        $data['sigle'] = $ur->getSigle();
        $data['cheminLogo'] = $ur->getCheminLogo();
        $data['RNSR'] = $ur->getRNSR();

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
        $object->setSigle($data['sigle']);
        $object->setCheminLogo($data['cheminLogo']);
        $object->setRNSR($data['RNSR']);

        return $object;
    }
}