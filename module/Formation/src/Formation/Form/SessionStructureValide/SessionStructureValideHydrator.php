<?php

namespace Formation\Form\SessionStructureValide;

use Formation\Entity\Db\SessionStructureValide;
use Laminas\Hydrator\HydratorInterface;
use Structure\Entity\Db\Structure;
use Structure\Service\Structure\StructureServiceAwareTrait;

class SessionStructureValideHydrator implements HydratorInterface {
    use StructureServiceAwareTrait;

    /**
     * @param SessionStructureValide $object
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
     * @param SessionStructureValide $object
     * @return SessionStructureValide
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