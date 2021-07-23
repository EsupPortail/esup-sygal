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
        $data['estFerme'] = $ed->getStructure()->estFermee();
        $data['id_ref'] = $ed->getStructure()->getIdRef();
        $data['theme'] = $ed->getTheme();
        $data['offre-these'] = $ed->getOffreThese();

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
        $theme = (isset($data['theme']) AND trim($data['theme']) !== '')?trim($data['theme']):null;
        $offreThese = (isset($data['offre-these']) AND trim($data['offre-these']) !== '')?trim($data['offre-these']):null;

        /** @var EcoleDoctorale $object */
        $object = parent::hydrate($data, $ed);

        $object->setLibelle($data['libelle']);
        $object->getStructure()->setCode($data['code']);
        $object->setSigle($data['sigle']);
        $object->setCheminLogo($data['cheminLogo']);
        $object->getStructure()->setIdRef($data['id_ref']);
        $object->setTheme($theme);
        $object->setOffreThese($offreThese);
        if (isset($data['estFerme']) AND $data['estFerme'] === "1") $object->getStructure()->setEstFermee(true); else $object->getStructure()->setEstFermee(false);

        return $object;
    }
}