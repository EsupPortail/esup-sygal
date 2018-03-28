<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RecapBu;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class CreationUtilisateurHydrator implements HydratorInterface, EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;

    /**
     * @param array $data
     * @param Individu $individu
     * @return Individu
     * @throws OptimisticLockException
     */
    public function hydrate(array $data, $individu) {

        $individu->setCivilite($data['civilite']);
        $individu->setNomUsuel($data['nomUsuel']);
        $individu->setNomPatronymique($data['nomPatronymique']);
        $individu->setPrenom1($data['prenom']);
        $individu->setEmail($data['email']);

        return $individu;
    }

    /**
     * @param Individu $individu
     * @return array
     */
    public function extract($individu) {

        $data = [];
        $data['civilite']        = $individu->getCivilite();
        $data['nomUsuel']        = $individu->getNomUsuel();
        $data['nomPatronymique'] = $individu->getNomPatronymique();
        $data['prenom']          = $individu->getPrenom1();
        $data['email']           = $individu->getEmail();

        return $data;
    }
}
