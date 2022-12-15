<?php

namespace These\Service\Acteur;

use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use Soutenance\Entity\Qualite;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\Acteur;
use These\Entity\Db\Repository\ActeurRepository;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class ActeurService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return ActeurRepository
     */
    public function getRepository()
    {
        /** @var ActeurRepository $repo */
        $repo = $this->entityManager->getRepository(Acteur::class);

        return $repo;
    }

    /**
     * @param Acteur[] $acteurs
     * @return Acteur[]
     */
    public function filterActeursPresidentJury(array $acteurs): array
    {
        return array_filter($acteurs, function(Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_PRESIDENT_JURY;
        });
    }

    /**
     * @param Acteur[] $acteurs
     * @return Acteur[]
     */
    public function filterActeursRapporteurJury(array $acteurs): array
    {
        return array_filter($acteurs, function(Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY;
        });
    }

    /**
     * @param Acteur[] $acteurs
     * @return Acteur[]
     */
    public function filterActeursDirecteurThese(array $acteurs): array
    {
        return array_filter($acteurs, function(Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_DIRECTEUR_THESE;
        });
    }

    /**
     * @param Acteur[] $acteurs
     * @return Acteur[]
     */
    public function filterActeursCoDirecteurThese(array $acteurs): array
    {
        return array_filter($acteurs, function(Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_CODIRECTEUR_THESE;
        });
    }

    /**
     * @param Individu $individu
     * @return Acteur[]
     */
    public function getRapporteurDansTheseEnCours(Individu $individu)
    {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder('acteur')
            ->addSelect('these')->join('acteur.these', 'these')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('acteur.histoDestruction is null')
            ->andWhere('these.etatThese = :encours')
            ->andWhere('acteur.individu = :individu')
            ->andWhere('role.code = :rapporteurJury OR role.code = :rapprteurAbsent')
            ->setParameter('encours', These::ETAT_EN_COURS)
            ->setParameter('individu', $individu)
            ->setParameter('rapporteurJury', Role::CODE_RAPPORTEUR_JURY)
            ->setParameter('rapprteurAbsent', Role::CODE_RAPPORTEUR_ABSENT)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @return Acteur
     */
    public function ajouterCoEncradrant(These $these, Individu $individu)
    {
        $etablissement = $these->getEtablissement();
        $role = $this->getRoleService()->getRepository()->findOneByCodeAndStructureConcrete(Role::CODE_CO_ENCADRANT, $etablissement);
        $source = $this->sourceService->fetchApplicationSource();

        $acteur = new Acteur();
        $acteur->setIndividu($individu);
        $acteur->setThese($these);
        $acteur->setRole($role);
        $acteur->setSource($source);
        $acteur->setSourceCode($this->sourceCodeStringHelper->addPrefixTo("CoEncadrement_" . $these->getId() ."_". $individu->getId(), $source->getCode()));
        $acteur->setEtablissement($etablissement);

        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $acteur->setHistoCreation($date);
            $acteur->setHistoCreateur($user);
            $this->getEntityManager()->persist($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }

        return $acteur;
    }

    /**
     * @param Acteur $acteur
     * @return Acteur
     */
    public function create(Acteur $acteur) : Acteur
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $acteur->setHistoModification($date);
            $acteur->setHistoModificateur($user);
            $this->getEntityManager()->persist($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }

        return $acteur;
    }



    /**
     * @param Acteur $acteur
     * @return Acteur
     */
    public function update(Acteur $acteur)  :Acteur
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $acteur->setHistoModification($date);
            $acteur->setHistoModificateur($user);
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }

        return $acteur;
    }

    /**
     * @param Acteur $acteur
     * @return Acteur
     */
    public function historise(Acteur $acteur)  :Acteur
    {
        try {
            $acteur->historiser();
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }
        return $acteur;
    }

    /**
     * @param Acteur $acteur
     * @return Acteur
     */
    public function restore(Acteur $acteur)  :Acteur
    {
        try {
            $acteur->dehistoriser();
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur");
        }
        return $acteur;
    }

    /**
     * @param Acteur $acteur
     * @return Acteur
     */
    public function delete(Acteur $acteur) : Acteur
    {
        try {
            $this->getEntityManager()->remove($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un acteur");
        }

        return $acteur;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Acteur
     */
    public function getRequestedActeur(AbstractActionController $controller, string $param='acteur')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Acteur $acteur */
        $acteur = $this->getRepository()->find($id);
        return $acteur;
    }

    /**
     * @param These $these
     * @param Individu $individu
     * @param string $roleCode
     * @param Qualite $qualite
     * @param Etablissement $etablissement
     * @return Acteur
     */
    public function creerOrModifierActeur(These $these, Individu $individu, string $roleCode, Qualite $qualite, Etablissement $etablissement) : Acteur
    {
        $acteur = $this->getRepository()->findActeurByIndividuAndThese($individu, $these);
        $role = $this->getRoleService()->getRepository()->findByCode($roleCode);
        if ($acteur === null OR $acteur === false) {
            $acteur = new Acteur();
            $acteur->setThese($these);
            $acteur->setIndividu($individu);
            $acteur->setRole($role);
            $acteur->setSource($this->sourceService->fetchApplicationSource());
            $acteur->setSourceCode('ACTEUR_' . $these->getId() . "_" . $individu->getId(). "_" . uniqid());
            $this->create($acteur);

        }
        $acteur->setRole($role);
        $acteur->setQualite($qualite);
        $acteur->setEtablissement($etablissement);
        $this->update($acteur);
        return $acteur;
    }

}