<?php

namespace Soutenance\Form\Membre;

use Soutenance\Entity\Membre;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

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
        $membre->setQualite($this->getQualiteService()->getQualiteById($data['qualite']));
        $membre->setEtablissement($data['etablissement']);
        $membre->setExterieur($data['exterieur']);
        if ($data['rapporteur'] && $data['membre']) {
            $membre->setRole(Membre::RAPPORTEUR);
        } else {
            if ($data['rapporteur']) {
                $membre->setRole(Membre::RAPPORTEUR_ABSENT);
            }
            if ($data['membre']) {
                $membre->setRole(Membre::MEMBRE);
            }
        }

        $membre->setEmail($data['email']);
        $membre->setVisio($data['visio']);
        return $membre;
    }

    /**
     * @param Membre $membre
     * @return array
     */
    public function extract($membre) {

        $data = [];
        $data['sexe']               = $membre->getGenre();
        $data['prenom']             = $membre->getPrenom();
        $data['nom']                = $membre->getNom();
        $data['qualite']            = $membre->getQualite();
        $data['etablissement']      = $membre->getEtablissement();
        $data['exterieur']          = $membre->getExterieur();
        switch($membre->getRole()) {
            case Membre::MEMBRE :
                $data['membre'] = 1;
                break;
            case Membre::RAPPORTEUR_ABSENT :
                $data['rapporteur'] = 1;
                break;
            case Membre::RAPPORTEUR :
                $data['membre'] = 1;
                $data['rapporteur'] = 1;
                break;
        }
        $data['email']              = $membre->getEmail();
        $data['visio']              = $membre->isVisio();
        return $data;
    }
}
