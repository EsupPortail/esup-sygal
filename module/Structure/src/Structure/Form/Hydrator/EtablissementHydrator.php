<?php

namespace Structure\Form\Hydrator;

use Structure\Entity\Db\Etablissement;

class EtablissementHydrator extends StructureHydrator
{
    /**
     * @param Etablissement $etablissement
     * @return array
     */
    public function extract($etablissement): array
    {
        $data = parent::extract($etablissement);

        $data['estMembre'] = $etablissement->estMembre();
        $data['estAssocie'] = $etablissement->estAssocie();
        $data['estInscription'] = $etablissement->estInscription();
        $data['estCed'] = $etablissement->estCed();

        $data['adresse'] = $etablissement->getStructure()->getAdresse();
        $data['telephone'] = $etablissement->getStructure()->getTelephone();
        $data['fax'] = $etablissement->getStructure()->getFax();
        $data['email'] = $etablissement->getStructure()->getEmail();
        $data['siteWeb'] = $etablissement->getStructure()->getSiteWeb();

        return $data;
    }

    /**
     * @param array $data
     * @param Etablissement $etablissement
     * @return Etablissement
     */
    public function hydrate(array $data, $etablissement): Etablissement
    {
        if (!isset($data['id']) || $data['id'] === "") $data['id'] = null;

        /** @var Etablissement $object */
        $object = parent::hydrate($data, $etablissement);

        $object->getStructure()->setAdresse($data['adresse'] ?: null);
        $object->getStructure()->setTelephone($data['telephone'] ?: null);
        $object->getStructure()->setFax($data['fax'] ?: null);
        $object->getStructure()->setEmail($data['email'] ?: null);
        $object->getStructure()->setSiteWeb($data['siteWeb'] ?: null);

        return $object;
    }
}