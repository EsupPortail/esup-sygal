<?php

namespace Application\Service\Utilisateur;

use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use UnicaenLdap\Entity\People;

/**
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 */
class UtilisateurService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Utilisateur::class);
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
        $this->getEntityManager()->flush($entity);

        return $entity;
    }
}