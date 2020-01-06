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
use UnicaenAuth\Entity\Db\UserInterface;
use UnicaenApp\Exception\RuntimeException;
use Zend\Mvc\Controller\AbstractActionController;

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
     * @param UserWrapper   $userWrapper
     * @param Etablissement $etablissement
     * @param Utilisateur   $utilisateur   Auteur éventuel de la création
     * @return Individu
     */
    public function createIndividuFromUserWrapperAndEtab(UserWrapper $userWrapper,
                                                         Etablissement $etablissement,
                                                         Utilisateur $utilisateur = null)
    {
        $sourceCode = $this->sourceCodeStringHelper->generateSourceCodeFromUserWrapperAndEtab($userWrapper, $etablissement);

        $entity = new Individu();
        $entity->setEtablissement($etablissement);
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
     */
    public function updateIndividuFromUserWrapper(Individu $entity, UserWrapper $userWrapper)
    {
        $etablissement = $entity->getEtablissement();

        $sourceCode = $this->sourceCodeStringHelper->generateSourceCodeFromUserWrapperAndEtab($userWrapper, $etablissement);

        $entity->setSourceCode($sourceCode);
        $entity->setSupannId($userWrapper->getSupannId());
        $entity->setEmail($userWrapper->getEmail());

        $entity->setHistoModificateur($this->getAppPseudoUtilisateur());

        try {
            $this->getEntityManager()->flush($entity);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Impossible d'enregistrer l'Individu", null, $e);
        }
    }

    /**
     * Met à jour le SOURCE_CODE et l'Etablissement d'un Individu.
     *
     * @param Individu      $entity
     * @param Etablissement $etablissement
     */
    public function updateIndividuFromEtab(Individu $entity, Etablissement $etablissement)
    {
        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($entity->getSupannId(), $etablissement);

        $entity->setSourceCode($sourceCode);
        $entity->setEtablissement($etablissement);

        $entity->setHistoModificateur($this->getAppPseudoUtilisateur());

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

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Individu
     */
    public function getRequestedIndividu($controller, $param = 'individu')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Individu $individu */
        $individu = $this->getRepository()->find($id);
        return $individu;

    }
}