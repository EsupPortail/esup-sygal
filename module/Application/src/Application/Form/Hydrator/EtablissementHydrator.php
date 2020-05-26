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
        $data['code'] = $etablissement->getStructure()->getCode();
        $data['sigle'] = $etablissement->getSigle();
        $data['domaine'] = $etablissement->getDomaine();
        $data['estMembre'] = $etablissement->estMembre();
        $data['estAssocie'] = $etablissement->estAssocie();
        $data['cheminLogo'] = $etablissement->getCheminLogo();
        $data['estFerme'] = $etablissement->getStructure()->isFerme();
        $data['adresse'] = $etablissement->getStructure()->getAdresse();
        $data['telephone'] = $etablissement->getStructure()->getTelephone();
        $data['fax'] = $etablissement->getStructure()->getFax();
        $data['email'] = $etablissement->getStructure()->getEmail();
        $data['siteWeb'] = $etablissement->getStructure()->getSiteWeb();

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
        $object->setSigle($data['sigle']);
        $object->setDomaine($data['domaine']);
        $object->getStructure()->setCode($data['code']);
        $object->getStructure()->setAdresse($data['adresse']);
        $object->getStructure()->setTelephone($data['telephone']);
        $object->getStructure()->setFax($data['fax']);
        $object->getStructure()->setEmail($data['email']);
        $object->getStructure()->setSiteWeb($data['siteWeb']);
        $object->setEstMembre($data['estMembre']);
        $object->setEstAssocie($data['estAssocie']);
        $object->setCheminLogo($data['cheminLogo']);
        if (isset($data['estFerme']) AND $data['estFerme'] === "1") $object->getStructure()->setFerme(true); else $object->getStructure()->setFerme(false);
        return $object;
    }
}