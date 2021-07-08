<?php

namespace Formation\Form\EnqueteQuestion;

use Formation\Entity\Db\EnqueteQuestion;
use Zend\Hydrator\HydratorInterface;

class EnqueteQuestionHydrator implements HydratorInterface {


    /**
     * @param EnqueteQuestion $object
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
     * @param EnqueteQuestion $object
     * @return EnqueteQuestion
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