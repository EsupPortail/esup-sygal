<?php

namespace Application\Service\UniteRecherche;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\EtablissementRattachement;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\Repository\UniteRechercheRepository;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Entity\Db\Utilisateur;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method UniteRecherche|null findOneBy(array $criteria, array $orderBy = null)
 */
class UniteRechercheService extends BaseService implements RoleServiceAwareInterface
{
    use RoleServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return UniteRechercheRepository
     */
    public function getRepository()
    {
        /** @var UniteRechercheRepository $repo */
        $repo = $this->entityManager->getRepository(UniteRecherche::class);

        return $repo;
    }

    /**
     * @param UniteRecherche $unite
     * @return EtablissementRattachement[]
     */
    public function findEtablissementRattachement(UniteRecherche $unite)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->addSelect("e, s")
            ->join("er.etablissement", "e")
            ->join("e.structure", "s")
            ->andWhere("er.unite = :unite")
            ->orderBy("s.libelle")
            ->setParameter("unite", $unite);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function existEtablissementRattachement($unite, $etablissement)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    /**
     * @param int $id
     * @return Individu[]
     */
    public function getIndividuByUniteRechercheId($id)
    {
        $unite = $this->getRepository()->findOneBy(['id' => $id]);
        $individus = $this->roleService->getIndividuByStructure($unite->getStructure());

        return $individus;
    }

    /**
     * Historise une ED.
     *
     * @param UniteRecherche $ur
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(UniteRecherche $ur, Utilisateur $destructeur)
    {
        $ur->historiser($destructeur);
        $ur->getStructure()->historiser($destructeur);

        $this->flush($ur);
    }

    public function undelete(UniteRecherche $ur)
    {
        $ur->dehistoriser();
        $ur->getStructure()->dehistoriser();

        $this->flush($ur);
    }

    public function create(UniteRecherche $structureConcrete, Utilisateur $createur)
    {
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => 'etablissement']);

        $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        $structureConcrete->setSourceCode($sourceCode);
        $structureConcrete->setHistoCreateur($createur);

        $structure = $structureConcrete->getStructure();
        $structure->setTypeStructure($typeStructure);
        $structure->setSourceCode($sourceCode);

        $this->entityManager->beginTransaction();

        $this->entityManager->persist($structure);
        $this->entityManager->persist($structureConcrete);
        try {
            $this->entityManager->flush($structure);
            $this->entityManager->flush($structureConcrete);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR '$structure'", null, $e);
        }

        return $structureConcrete;
    }

    public function update(UniteRecherche $ur)
    {
        $this->flush($ur);

        return $ur;
    }

    public function setLogo(UniteRecherche $unite, $cheminLogo)
    {
        $unite->setCheminLogo($cheminLogo);
        $this->flush($unite);

        return $unite;
    }

    public function deleteLogo(UniteRecherche $unite)
    {
        $unite->setCheminLogo(null);
        $this->flush($unite);

        return $unite;
    }

    private function persist(UniteRecherche $ur)
    {
        $this->getEntityManager()->persist($ur);
        $this->getEntityManager()->persist($ur->getStructure());
    }

    private function flush(UniteRecherche $ur)
    {
        try {
            $this->getEntityManager()->flush($ur);
            $this->getEntityManager()->flush($ur->getStructure());
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }

    /** ETABLISSEMENT DE RATTACHEMENT **/

    /**
     * @param UniteRecherche $unite
     * @param Etablissement  $etablissement
     * @throws OptimisticLockException
     */
    public function addEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $er = new EtablissementRattachement();
        $er->setUnite($unite);
        $er->setEtablissement($etablissement);
        $this->getEntityManager()->persist($er);
        $this->getEntityManager()->flush($er);
    }

    /**
     * @param UniteRecherche $unite
     * @param Etablissement  $etablissement
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);
        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            $this->getEntityManager()->remove($result);
            $this->getEntityManager()->flush($result);
        }
    }
}