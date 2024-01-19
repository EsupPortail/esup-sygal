<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Profil;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class ProfilHydrator implements HydratorInterface
{
    use StructureServiceAwareTrait;

    /**
     * @param Profil $object
     * @return array
     */
    public function extract($object): array
    {
        return [
            'libelle' => $object->getLibelle(),
            'code' => $object->getRoleCode(),
            'structure' => ($object->getStructureType())?$object->getStructureType()->getCode():null,
            'description' => $object->getDescription(),
        ];
    }

    /**
     * @param array $data
     * @param Profil $object
     * @return Profil
     */
    public function hydrate(array $data, $object)
    {
        $object->setLibelle($data['libelle']);
        $object->setRoleCode($data['code']);
        $object->setDescription($data['description']);

        $type = $this->getStructureService()->fetchTypeStructureByCode($data['structure']);
        $object->setStructureType($type);

        return $object;
    }

}