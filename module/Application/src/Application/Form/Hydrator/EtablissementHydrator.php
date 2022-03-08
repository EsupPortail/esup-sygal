<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Etablissement;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class EtablissementHydrator extends DoctrineObject
{
    /**
     * Extract values from an object
     *
     * @param  Etablissement $etablissement
     * @return array
     */
    public function extract($etablissement): array
    {
        $data = parent::extract($etablissement);

        $data['libelle'] = $etablissement->getLibelle();
        $data['code'] = $etablissement->getStructure()->getCode();
        $data['sigle'] = $etablissement->getSigle();
        $data['domaine'] = $etablissement->getDomaine();
        $data['estMembre'] = $etablissement->estMembre();
        $data['estAssocie'] = $etablissement->estAssocie();
        $data['estInscription'] = $etablissement->estInscription();
        $data['cheminLogo'] = $etablissement->getCheminLogo();
        $data['estFerme'] = $etablissement->getStructure()->estFermee();
        $data['adresse'] = $etablissement->getStructure()->getAdresse();
        $data['telephone'] = $etablissement->getStructure()->getTelephone();
        $data['fax'] = $etablissement->getStructure()->getFax();
        $data['email'] = $etablissement->getStructure()->getEmail();
        $data['siteWeb'] = $etablissement->getStructure()->getSiteWeb();
        $data['id_ref'] = $etablissement->getStructure()->getIdRef();

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
        $object->getStructure()->setIdRef($data['id_ref']);
        $object->setEstMembre($data['estMembre']);
        $object->setEstAssocie($data['estAssocie']);
        $object->setEstInscription($data['estInscription']);
        $object->setCheminLogo($data['cheminLogo']);
        if (isset($data['estFerme']) AND $data['estFerme'] === "1") $object->getStructure()->setEstFermee(true); else $object->getStructure()->setEstFermee(false);
        return $object;
    }
}