<?php

namespace Structure\Form\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Structure\Entity\Db\StructureConcreteInterface;

abstract class StructureConcreteHydrator extends DoctrineObject
{
    /**
     * @param StructureConcreteInterface $object
     * @return array
     */
    public function extract($object): array
    {
        $data = parent::extract($object);

        $structure = $object->getStructure();

        $data['libelle'] = $structure->getLibelle();
        $data['code'] = $structure->getCode();
        $data['sigle'] = $structure->getSigle();
        $data['cheminLogo'] = $structure->getCheminLogo();
        $data['estFerme'] = $structure->estFermee();
        $data['id_ref'] = $structure->getIdRef();
        $data['id_hal'] = $structure->getIdHal();

        return $data;
    }

    /**
     * @param array $data
     * @param StructureConcreteInterface $object
     * @return StructureConcreteInterface
     */
    public function hydrate(array $data, $object): StructureConcreteInterface
    {
        // la gestion du logo ne peut pas être faite ici
        unset($data['cheminLogo']);

        /** @var StructureConcreteInterface $object */
        $object = parent::hydrate($data, $object);

        $structure = $object->getStructure();

        // certains champs majeurs sont facultatifs lorsqu'ils sont forcés (ex : établisssement CED)
        if (isset($data['libelle'])) {
            $structure->setLibelle($data['libelle']);
        }
        if (isset($data['sigle'])) {
            $structure->setSigle($data['sigle']);
        }
        if (isset($data['code'])) {
            $structure->setCode($data['code']);
        }
        if (isset($data['sourceCode'])) {
            $structure->setSourceCode($data['sourceCode']);
        } else {
            $structure->setSourceCode($object->getSourceCode());
        }

        $structure->setIdRef($data['id_ref'] ?? null);
        $structure->setIdHal($data['id_hal'] ?? null);
        $structure->setEstFermee(isset($data['estFerme']) and $data['estFerme'] === "1");

        return $object;
    }
}