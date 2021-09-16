<?php

namespace Formation\Form\EnqueteCategorie;

use Formation\Entity\Db\EnqueteCategorie;
use Zend\Hydrator\HydratorInterface;

class EnqueteCategorieHydrator implements HydratorInterface {


    /**
     * @param EnqueteCategorie $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'libelle' => $object->getLibelle(),
            'description' => $object->getDescription(),
            'ordre' => $object->getOrdre(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param EnqueteCategorie $object
     * @return EnqueteCategorie
     */
    public function hydrate(array $data, $object)
    {
        $libelle = (isset($data['libelle']) AND trim($data['libelle']) !== '')?trim($data['libelle']):null;
        $description = (isset($data['description']) AND trim($data['description']) !== '')?trim($data['description']):null;
        $ordre = (isset($data['ordre']))?trim($data['ordre']):null;

        $object->setLibelle($libelle);
        $object->setDescription($description);
        $object->setOrdre($ordre);

        return $object;
    }

}