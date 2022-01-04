<?php

namespace Soutenance\Form\Membre;

use Soutenance\Entity\Membre;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class MembreHydrator implements HydratorInterface
{
    use QualiteServiceAwareTrait;

    /**
     * @param array $data
     * @param Membre $membre
     * @return Membre
     */
    public function hydrate(array $data, $membre) {

        $membre->setGenre($data['sexe']);
        $membre->setPrenom($data['prenom']);
        $membre->setNom($data['nom']);
        $membre->setQualite($this->getQualiteService()->getQualite($data['qualite']));
        $membre->setEtablissement($data['etablissement']);
        $membre->setAdresse($data['adresse']);
        $membre->setExterieur($data['exterieur']);
        $membre->setVisio($data['visio']);
        $membre->setRole($data['role']);
        $membre->setEmail($data['email']);
        return $membre;
    }

    /**
     * @param Membre $membre
     * @return array
     */
    public function extract($membre): array {

        $data = [];
        $data['sexe']               = $membre->getGenre();
        $data['prenom']             = $membre->getPrenom();
        $data['nom']                = $membre->getNom();
        $data['qualite']            = ($membre->getQualite())?$membre->getQualite()->getId():null;
        $data['etablissement']      = $membre->getEtablissement();
        $data['adresse']            = $membre->getAdresse();
        $data['exterieur']          = $membre->getExterieur();
        $data['visio']              = $membre->isVisio();
        $data['role']               = $membre->getRole();
        $data['email']              = $membre->getEmail();

        return $data;
    }
}
