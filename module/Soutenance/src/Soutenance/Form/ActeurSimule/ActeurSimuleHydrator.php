<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ActeurSimuleHydrator implements HydratorInterface {
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * @param Acteur $object
     * @return array
     */
    public function extract($object)
    {
        $data  = [
            'individu' => ($object->getIndividu()) ? $object->getIndividu()->getId(): null,
            'role' => ($object->getRole() !== null)?$object->getRole()->getId():null,
            'qualite' => $object->getQualite(),
            'etablissement' => ($object->getEtablissement() !== null)?$object->getEtablissement()->getId():null,
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Acteur $object
     * @return Acteur
     */
    public function hydrate(array $data, $object)
    {
        $role = $this->getRoleService()->getRepository()->find($data['role']);
        $etablissement = $this->getEtablissementService()->getRepository()->find($data['etablissement']);
        /** @var Individu $individu */
        $individu = $this->getIndividuService()->getRepository()->find($data['individu']);

        $object->setIndividu($individu);
        $object->setRole($role);
        $object->setQualite($data['qualite']);
        $object->setEtablissement($etablissement);
        return $object;
    }


}