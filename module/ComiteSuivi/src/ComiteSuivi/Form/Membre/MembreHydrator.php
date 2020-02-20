<?php

namespace ComiteSuivi\Form\Membre;

use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use ComiteSuivi\Entity\Db\Membre;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class MembreHydrator implements HydratorInterface {
    use ComiteSuiviServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * @param Membre $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'comite' => ($object->getComite())?$object->getComite()->getId():null,
            'prenom' => $object->getPrenom(),
            'nom' => $object->getNom(),
            'etablissement' => $object->getEtablissement(),
            'role' => ($object->getRole())?$object->getRole()->getCode():null,
            'email' => $object->getEmail(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Membre $object
     * @return Membre
     */
    public function hydrate(array $data, $object)
    {
        /** @var Role $role */
        $comite = $this->getComiteSuiviService()->getComiteSuivi($data['comite']);
        $role = $this->getRoleService()->getRepository()->findOneBy(['code' => $data['role']]);

        $object->setComite($comite);
        $object->setPrenom(isset($data['prenom'])?$data['prenom']:null);
        $object->setNom(isset($data['nom'])?$data['nom']:null);
        $object->setEtablissement(isset($data['etablissement'])?$data['etablissement']:null);
        $object->setRole($role);
        $object->setEmail(isset($data['email'])?$data['email']:null);

        return $object;
    }


}