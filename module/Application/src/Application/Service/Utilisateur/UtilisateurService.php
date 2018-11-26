<?php

namespace Application\Service\Utilisateur;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\UtilisateurRepository;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenLdap\Entity\People;

class UtilisateurService extends BaseService
{
    /**
     * @return UtilisateurRepository
     */
    public function getRepository()
    {
        /** @var UtilisateurRepository $repo */
        $repo = $this->entityManager->getRepository(Utilisateur::class);

        return $repo;
    }

    /**
     * @param People $people
     * @return Utilisateur
     */
    public function createFromPeople(People $people)
    {
        $entity = new Utilisateur();
        $entity->setDisplayName($people->getNomComplet(true));
        $entity->setEmail($people->get('mail'));
        $entity->setUsername($people->get('supannAliasLogin'));
        $entity->setPassword('ldap');
        $entity->setState(1);

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }

        return $entity;
    }

    /**
     * @param Individu $individu
     * @return Utilisateur
     */
    public function createFromIndividu(Individu $individu)
    {
        if ($individu->getSupannId() == null) {
            throw new RuntimeException("Le supannId de l'individu $individu (id={$individu->getId()}) est null.");
        }

        $etablissementSource = $individu->getSource()->getEtablissement();
        $username = $individu->getSupannId() . '@' . $etablissementSource->getDomaine();

        $utilisateur = new Utilisateur();
        $utilisateur->setDisplayName($individu->getNomComplet());
        $utilisateur->setEmail($individu->getEmail());
        $utilisateur->setUsername($username);
        $utilisateur->setPassword('shib');
        $utilisateur->setState(1);
        $utilisateur->setIndividu($individu);

        $this->getEntityManager()->persist($utilisateur);
        try {
            $this->getEntityManager()->flush($utilisateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }

        return $utilisateur;
    }

    /**
     * Renseigne l'individu correspondant à un utilisateur en bdd.
     *
     * @param Individu    $individu
     * @param Utilisateur $utilisateur
     */
    public function setIndividuForUtilisateur(Individu $individu, Utilisateur $utilisateur)
    {
        $utilisateur->setIndividu($individu);

        try {
            $this->getEntityManager()->flush($utilisateur);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'utilisateur", null, $e);
        }
    }
}