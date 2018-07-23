<?php

namespace Soutenance\Form\SoutenanceMembre;

use DateTime;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class SoutenanceMembreHydrator implements HydratorInterface
{
    use MembreServiceAwareTrait;
    /**
     * @param array $data
     * @param Membre $membre
     * @return Membre
     */
    public function hydrate(array $data, $membre) {

        $membre->setGenre($data['sexe']);
        $membre->setDenomination($data['denomination']);
        $membre->setQualite($this->getMembreService()->getQualiteById($data['qualite']));
        $membre->setEtablissement($data['etablissement']);
        $membre->setExterieur($data['exterieur']);
        $membre->setRole($data['role']);

        return $membre;
    }

    /**
     * @param Membre $membre
     * @return array
     */
    public function extract($membre) {

        $data = [];
        $data['sexe']               = $membre->getGenre();
        $data['denomination']       = $membre->getDenomination();
        $data['qualite']            = $membre->getQualite();
        $data['etablissement']      = $membre->getEtablissement();
        $data['exterieur']          = $membre->getExterieur();
        $data['role']               = $membre->getRole();

        return $data;
    }
}
