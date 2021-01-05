<?php

namespace Application\Service\Acteur;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\ActeurRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
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
     * @param Individu $individu
     * @return Acteur[]
     */
    public function getRapporteurDansTheseEnCours(Individu $individu)
    {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder('acteur')
            ->addSelect('these')->join('acteur.these', 'these')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('1 = pasHistorise(acteur)')
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
        $role = $this->getRoleService()->getRepository()->findByCodeAndEtablissement(Role::CODE_CO_ENCADRANT, $etablissement);
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
    public function delete(Acteur $acteur)
    {
        try {
            $this->getEntityManager()->remove($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un acteur");
        }

        return $acteur;
    }



}