<?php

namespace Formation\Form\Module;

use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Formation\Entity\Db\Formation;
use Zend\Hydrator\HydratorInterface;

class ModuleHydrator implements HydratorInterface {
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use StructureServiceAwareTrait;

    /**
     * @param Formation $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'libelle' => $object->getLibelle(),
            'description' => $object->getDescription(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Formation $object
     * @return Formation
     */
    public function hydrate(array $data, $object)
    {
        $libelle = (isset($data['libelle']) AND trim($data['libelle']) !== '')?trim($data['libelle']):null;
        $description = (isset($data['description']) AND trim($data['description']) !== '')?trim($data['description']):null;

        $object->setLibelle($libelle);
        $object->setDescription($description);
        return $object;
    }


}