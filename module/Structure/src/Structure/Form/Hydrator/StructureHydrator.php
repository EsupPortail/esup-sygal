<?php

namespace Structure\Form\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Structure\Entity\Db\StructureConcreteInterface;

abstract class StructureHydrator extends DoctrineObject
{
    /**
     * @param StructureConcreteInterface $structure
     * @return array
     */
    public function extract($structure): array
    {
        $data = parent::extract($structure);

        $data['libelle'] = $structure->getStructure()->getLibelle();
        $data['code'] = $structure->getStructure()->getCode();
        $data['sigle'] = $structure->getStructure()->getSigle();
        $data['cheminLogo'] = $structure->getStructure()->getCheminLogo();
        $data['estFerme'] = $structure->getStructure()->estFermee();
        $data['id_ref'] = $structure->getStructure()->getIdRef();
        $data['id_hal'] = $structure->getStructure()->getIdHal();

        return $data;
    }

    /**
     * @param array $data
     * @param StructureConcreteInterface $structure
     * @return StructureConcreteInterface
     */
    public function hydrate(array $data, $structure): StructureConcreteInterface
    {
        /** @var StructureConcreteInterface $object */
        $object = parent::hydrate($data, $structure);

        $object->getStructure()->setLibelle($data['libelle']);
        $object->getStructure()->setSigle($data['sigle']);
        $object->getStructure()->setCode($data['code']);
        $object->getStructure()->setIdRef($data['id_ref']);
        $object->getStructure()->setIdHal($data['id_hal']);
        $object->getStructure()->setCheminLogo($data['cheminLogo']??null);
        $object->getStructure()->setEstFermee(isset($data['estFerme']) and $data['estFerme'] === "1");

        return $object;
    }
}