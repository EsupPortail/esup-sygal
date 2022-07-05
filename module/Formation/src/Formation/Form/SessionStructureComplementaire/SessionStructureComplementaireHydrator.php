<?php

namespace Formation\Form\SessionStructureComplementaire;

use Formation\Entity\Db\SessionStructureComplementaire;
use Laminas\Hydrator\HydratorInterface;
use Structure\Entity\Db\Structure;
use Structure\Service\Structure\StructureServiceAwareTrait;

class SessionStructureComplementaireHydrator implements HydratorInterface {
    use StructureServiceAwareTrait;

    /**
     * @param SessionStructureComplementaire $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [
            'structure' => ($object AND $object->getStructure())?$object->getStructure()->getId():null,
            'lieu' => ($object AND $object->getLieu())?$object->getLieu():null,
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param SessionStructureComplementaire $object
     * @return SessionStructureComplementaire
     */
    public function hydrate(array $data, object $object)
    {
        /** @var Structure|null $structure */
        $structure = (isset($data['structure']))?$this->getStructureService()->getRepository()->find($data['structure']):null;
        $lieu = (isset($data['lieu']) AND trim($data['lieu']) !== '')?trim($data['lieu']):null;

        $object->setStructure($structure);
        $object->setLieu($lieu);
        return $object;
    }


}