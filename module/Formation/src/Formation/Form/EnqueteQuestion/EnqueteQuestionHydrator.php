<?php

namespace Formation\Form\EnqueteQuestion;

use Formation\Entity\Db\EnqueteCategorie;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Service\EnqueteCategorie\EnqueteCategorieServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class EnqueteQuestionHydrator implements HydratorInterface {
    use EnqueteCategorieServiceAwareTrait;

    /**
     * @param EnqueteQuestion $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'libelle' => $object->getLibelle(),
            'description' => $object->getDescription(),
            'categorie' => ($object->getCategorie())?$object->getCategorie()->getId():null,
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
        /** @var EnqueteCategorie|null $categorie */
        $categorie = (isset($data['categorie']))?$this->getEnqueteCategorieService()->getRepository()->find($data['categorie']):null;
        $ordre = (isset($data['ordre']))?trim($data['ordre']):null;

        $object->setLibelle($libelle);
        $object->setDescription($description);
        $object->setCategorie($categorie);
        $object->setOrdre($ordre);

        return $object;
    }

}