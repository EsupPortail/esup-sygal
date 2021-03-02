<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\CreationUtilisateurInfos;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Hydrator\HydratorInterface;

class CreationUtilisateurHydrator implements HydratorInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param array $data
     * @param CreationUtilisateurInfos $infos
     * @return CreationUtilisateurInfos
     */
    public function hydrate(array $data, $infos) {

        $infos->setCivilite($data['civilite']);
        $infos->setNomUsuel($data['nomUsuel']);
        $infos->setNomPatronymique($data['nomPatronymique']);
        $infos->setPrenom($data['prenom']);
        $infos->setEmail($data['email']);
        $infos->setPassword($data['password']);

        return $infos;
    }

    /**
     * @param CreationUtilisateurInfos $infos
     * @return array
     */
    public function extract($infos) {

        $data = [];
        $data['civilite']        = $infos->getCivilite();
        $data['nomUsuel']        = $infos->getNomUsuel();
        $data['nomPatronymique'] = $infos->getNomPatronymique();
        $data['prenom']          = $infos->getPrenom();
        $data['email']           = $infos->getEmail();
        $data['password']        = $infos->getPassword();

        return $data;
    }
}
