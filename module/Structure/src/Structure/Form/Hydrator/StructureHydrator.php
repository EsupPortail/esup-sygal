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

        $data['libelle'] = $structure->getLibelle();
        $data['code'] = $structure->getStructure()->getCode();
        $data['sigle'] = $structure->getSigle();
        $data['cheminLogo'] = $structure->getCheminLogo();
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

        $object->setLibelle($data['libelle']);
        $object->setSigle($data['sigle']);
        $object->getStructure()->setCode($data['code']);
        $object->getStructure()->setIdRef($data['id_ref']);
        $object->getStructure()->setIdHal($data['id_hal']);
        $object->setCheminLogo($data['cheminLogo']);
        $object->getStructure()->setEstFermee(isset($data['estFerme']) and $data['estFerme'] === "1");

        return $object;
    }
}