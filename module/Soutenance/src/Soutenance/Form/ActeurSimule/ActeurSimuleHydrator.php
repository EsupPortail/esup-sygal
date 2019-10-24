<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Entity\Db\Acteur;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ActeurSimuleHydrator implements HydratorInterface {
    use EtablissementServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * @param Acteur $object
     * @return array
     */
    public function extract($object)
    {
        $data  = [
            'civilite' => $object->getIndividu()->getCivilite(),
            'prenom' => $object->getIndividu()->getPrenom(),
            'nom' => $object->getIndividu()->getNomUsuel(),
            'email' => $object->getIndividu()->getEmail(),
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

        $object->getIndividu()->setCivilite($data['civilite']);
        $object->getIndividu()->setPrenom($data['prenom']);
        $object->getIndividu()->setNomUsuel($data['nom']);
        $object->getIndividu()->setEmail($data['email']);
        $object->setRole($role);
        $object->setQualite($data['qualite']);
        $object->setEtablissement($etablissement);
        return $object;
    }


}