<?php

namespace Structure\Form\Hydrator;

use Structure\Entity\Db\Etablissement;

class EtablissementHydrator extends StructureConcreteHydrator
{
    /**
     * @param Etablissement $object
     * @return array
     */
    public function extract($object): array
    {
        $data = parent::extract($object);

        $data['estAssocie'] = $object->estAssocie();
        $data['estInscription'] = $object->estInscription();
        $data['estCed'] = $object->estCed();

        $structure = $object->getStructure();

        $data['adresse'] = $structure->getAdresse();
        $data['telephone'] = $structure->getTelephone();
        $data['fax'] = $structure->getFax();
        $data['email'] = $structure->getEmail();
        $data['siteWeb'] = $structure->getSiteWeb();

        return $data;
    }

    /**
     * @param array $data
     * @param Etablissement $object
     * @return Etablissement
     */
    public function hydrate(array $data, $object): Etablissement
    {
        if (!isset($data['id']) || $data['id'] === "") $data['id'] = null;

        $object = parent::hydrate($data, $object);

        $structure = $object->getStructure();

        $structure->setAdresse($data['adresse'] ?: null);
        $structure->setTelephone($data['telephone'] ?: null);
        $structure->setFax($data['fax'] ?: null);
        $structure->setEmail($data['email'] ?: null);
        $structure->setSiteWeb($data['siteWeb'] ?: null);

        return $object;
    }
}