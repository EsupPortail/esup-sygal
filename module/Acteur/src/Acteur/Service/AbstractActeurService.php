<?php

namespace Acteur\Service;

use Acteur\Entity\Db\AbstractActeur;
use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Entity\Db\Repository\ActeurHDRRepository;
use Acteur\Entity\Db\Repository\ActeurTheseRepository;
use Application\Entity\Db\Role;
use Application\Service\BaseService;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\QueryBuilder;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

class AbstractActeurService extends BaseService
{
    use ApplicationRoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    protected string $entityClass;

    public function getRepository(): ActeurTheseRepository|ActeurHDRRepository
    {
        /** @var ActeurTheseRepository|ActeurHDRRepository $repo */
        $repo = $this->entityManager->getRepository($this->entityClass);

        return $repo;
    }

    /**
     * @param ActeurThese[]|ActeurHDR[] $acteurs
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function filterActeursPresidentJury(array $acteurs): array
    {
        return array_filter($acteurs, function(AbstractActeur $a) {
            return $a->getRole()->getCode() === Role::CODE_PRESIDENT_JURY;
        });
    }

    /**
     * @param ActeurThese[]|ActeurHDR[] $acteurs
     * @return ActeurThese[]|ActeurHDR[]
     */
    public function filterActeursRapporteurJury(array $acteurs): array
    {
        return array_filter($acteurs, function(AbstractActeur $a) {
            return $a->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY;
        });
    }

    protected function getRapporteurDansEnCoursQb(Individu $individu): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('acteur')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('acteur.histoDestruction is null')
            ->andWhere('acteur.individu = :individu')
            ->andWhere('role.code = :rapporteurJury OR role.code = :rapprteurAbsent')
            ->setParameter('individu', $individu)
            ->setParameter('rapporteurJury', Role::CODE_RAPPORTEUR_JURY)
            ->setParameter('rapprteurAbsent', Role::CODE_RAPPORTEUR_ABSENT)
        ;
    }

    /**
     * Retourne une nouvelle instance de {@see ActeurThese} ou {@see ActeurHDR}.
     *
     * @param Individu $individu
     * @param Role|string $role
     * @param Etablissement|null $etablissement
     * @return AbstractActeur
     */
    public function newActeur(Individu $individu, Role|string $role, ?Etablissement $etablissement = null): AbstractActeur
    {
        if (is_string($role)) {
            $role = $this->applicationRoleService->getRepository()->findByCode($role);
        }

        $acteur = new $this->entityClass;
        $acteur->setIndividu($individu);
        $acteur->setRole($role);
        $acteur->setEtablissement($etablissement);
        $acteur->setSource($source = $this->sourceService->fetchApplicationSource());
        $acteur->setSourceCode($this->sourceCodeStringHelper->addPrefixTo(
            sprintf("ACTEUR_%s_%s_%s", $role->getCode(), $individu->getId(), uniqid()),
            $source->getCode()
        ));

        return $acteur;
    }

    /**
     * @deprecated Utiliser {@see self::save()}
     */
    protected function create(AbstractActeur $acteur): AbstractActeur
    {
        $this->save($acteur);

        return $acteur;
    }

    /**
     * @deprecated Utiliser {@see self::save()}
     */
    public function update(AbstractActeur $acteur): AbstractActeur
    {
        $this->save($acteur);

        return $acteur;
    }

    public function save(AbstractActeur $acteur): void
    {
        try {
            if ($acteur->getId() === null) {
                $this->getEntityManager()->persist($acteur);
            }
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur", null, $e);
        }
    }

    public function historise(AbstractActeur $acteur): AbstractActeur
    {
        try {
            $acteur->historiser();
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur", null, $e);
        }
        return $acteur;
    }

    public function restore(AbstractActeur $acteur): AbstractActeur
    {
        try {
            $acteur->dehistoriser();
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un acteur", null, $e);
        }
        return $acteur;
    }

    public function delete(AbstractActeur $acteur): AbstractActeur
    {
        try {
            $this->getEntityManager()->remove($acteur);
            $this->getEntityManager()->flush($acteur);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un acteur", null, $e);
        }

        return $acteur;
    }

    public function getRequestedActeur(AbstractActionController $controller, string $param = 'acteur'): AbstractActeur
    {
        $id = $controller->params()->fromRoute($param);
        /** @var AbstractActeur $acteur */
        $acteur = $this->getRepository()->find($id);
        return $acteur;
    }
}