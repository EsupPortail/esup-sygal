<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\IndividuRepository;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    /**
     * @return IndividuRepository
     */
    public function getRepository()
    {
        /** @var IndividuRepository $repo */
        $repo = $this->entityManager->getRepository(Individu::class);

        return $repo;
    }

    /**
     * @param People        $people
     * @param Etablissement $etablissement
     * @return Individu
     * @deprecated À supprimer car non utilisée
     */
    public function createIndividuFromPeopleAndEtab(People $people, Etablissement $etablissement)
    {
        $sns = (array)$people->get('sn');
        $usuel = array_pop($sns);
        $patro = array_pop($sns);
        if ($patro === null) $patro = $usuel;

        $entity = new Individu();
        $entity->setNomUsuel($usuel);
        $entity->setNomPatronymique($patro);
        $entity->setPrenom($people->get('givenName'));
        $entity->setCivilite($people->get('supannCivilite'));
        $entity->setEmail($people->get('mail'));

        $entity->setSourceCode($etablissement->prependPrefixTo($people->get('supannEmpId')));

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }

        return $entity;
    }

    /**
     * @param UserWrapper   $userWrapper
     * @param Etablissement $etablissement
     * @param Utilisateur   $utilisateur   Auteur éventuel de la création
     * @return Individu
     */
    public function createIndividuFromUserWrapperAndEtab(UserWrapper $userWrapper,
                                                         Etablissement $etablissement,
                                                         Utilisateur $utilisateur = null)
    {
        $sourceCode = $etablissement->prependPrefixTo($userWrapper->getSupannId());

        $entity = new Individu();
        $entity->setSupannId($userWrapper->getSupannId());
        $entity->setNomUsuel($userWrapper->getNom() ?: "X"); // NB: le nom est obligatoire mais pas forcément disponible
        $entity->setNomPatronymique($userWrapper->getNom());
        $entity->setPrenom($userWrapper->getPrenom());
        $entity->setCivilite($userWrapper->getCivilite());
        $entity->setEmail($userWrapper->getEmail());
        $entity->setSourceCode($sourceCode);
        $entity->setHistoCreateur($utilisateur);

        $this->getEntityManager()->persist($entity);
        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }

        return $entity;
    }

    /**
     * @param Individu    $entity
     * @param UserWrapper $userWrapper
     * @param Utilisateur $utilisateur
     */
    public function updateIndividuFromUserWrapper(Individu $entity,
                                                  UserWrapper $userWrapper,
                                                  Utilisateur $utilisateur)
    {
        $entity->setSupannId($userWrapper->getSupannId());
        $entity->setNomUsuel($userWrapper->getNom() ?: "X"); // NB: le nom est obligatoire mais quid si indisponible ?
        $entity->setNomPatronymique($userWrapper->getNom());
        $entity->setPrenom($userWrapper->getPrenom());
        $entity->setCivilite($userWrapper->getCivilite());
        $entity->setEmail($userWrapper->getEmail());
        $entity->setHistoModificateur($utilisateur);

        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }
    }

    public function existIndividuUtilisateurByEmail($email) {
        $exist_individu = $this->getEntityManager()->getRepository(Individu::class)->findOneBy(["email" => $email]);
        $exist_utilisateur = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);

        return ($exist_individu !== null || $exist_utilisateur !== null);
    }
}