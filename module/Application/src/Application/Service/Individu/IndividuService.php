<?php

namespace Application\Service\Individu;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\IndividuRepository;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Entity\UserInterface;
use UnicaenApp\Exception\RuntimeException;
use UnicaenLdap\Entity\People;

class IndividuService extends BaseService
{
    use UtilisateurServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    /**
     * @var Utilisateur
     */
    protected $appPseudoUtilisateur;

    /**
     * @return IndividuRepository
     */
    public function getRepository()
    {
        /** @var IndividuRepository $repo */
        $repo = $this->entityManager->getRepository(Individu::class);

        $repo->setSourceCodeStringHelper($this->sourceCodeStringHelper);

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

        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($people->get('supannEmpId'), $etablissement);
        $entity->setSourceCode($sourceCode);

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
        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($userWrapper->getSupannId(), $etablissement);

        $entity = new Individu();
        $entity->setSupannId($userWrapper->getSupannId());
        $entity->setNomUsuel($userWrapper->getNom() ?: "X"); // NB: le nom est obligatoire mais pas forcément disponible
        $entity->setNomPatronymique($userWrapper->getNom());
        $entity->setPrenom($userWrapper->getPrenom());
        $entity->setCivilite($userWrapper->getCivilite());
        $entity->setEmail($userWrapper->getEmail());
        $entity->setSourceCode($sourceCode);
        $entity->setHistoCreateur($utilisateur ?: $this->getAppPseudoUtilisateur());

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
                                                  Utilisateur $utilisateur = null)
    {
        $entity->setSupannId($userWrapper->getSupannId());
        $entity->setNomUsuel($userWrapper->getNom() ?: "X"); // NB: le nom est obligatoire mais quid si indisponible ?
        $entity->setNomPatronymique($userWrapper->getNom());
        $entity->setPrenom($userWrapper->getPrenom());
        $entity->setCivilite($userWrapper->getCivilite());
        $entity->setEmail($userWrapper->getEmail());
        $entity->setHistoModificateur($utilisateur ?: $this->getAppPseudoUtilisateur());

        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }
    }

    /**
     * Met à jour le SOURCE_CODE d'un individu à partir de l'établissement spécifié.
     *
     * @param Individu      $entity
     * @param Etablissement $etablissement
     * @param Utilisateur   $modificateur
     */
    public function updateIndividuSourceCodeFromEtab(Individu $entity,
                                                     Etablissement $etablissement,
                                                     Utilisateur $modificateur = null)
    {
        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($entity->getSupannId(), $etablissement);

        $entity->setSourceCode($sourceCode);
        $entity->setHistoModificateur($modificateur ?: $this->getAppPseudoUtilisateur());

        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }
    }

    /**
     * @return Utilisateur|UserInterface
     */
    private function getAppPseudoUtilisateur()
    {
        if ($this->appPseudoUtilisateur === null) {
            $this->appPseudoUtilisateur = $this->utilisateurService->fetchAppPseudoUtilisateur();
        }

        return $this->appPseudoUtilisateur;
    }

    public function existIndividuUtilisateurByEmail($email) {
        $exist_individu = $this->getEntityManager()->getRepository(Individu::class)->findOneBy(["email" => $email]);
        $exist_utilisateur = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(["email" => $email]);

        return ($exist_individu !== null || $exist_utilisateur !== null);
    }
}