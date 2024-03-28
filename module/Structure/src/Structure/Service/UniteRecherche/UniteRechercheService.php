<?php

namespace Structure\Service\UniteRecherche;

use Application\Entity\Db\Utilisateur;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\EtablissementRattachement;
use Structure\Entity\Db\Repository\UniteRechercheRepository;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method UniteRecherche|null findOneBy(array $criteria, array $orderBy = null)
 */
class UniteRechercheService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return UniteRechercheRepository
     */
    public function getRepository(): UniteRechercheRepository
    {
        /** @var UniteRechercheRepository $repo */
        $repo = $this->entityManager->getRepository(UniteRecherche::class);

        return $repo;
    }

    /**
     * @param UniteRecherche $unite
     * @return EtablissementRattachement[]
     */
    public function findEtablissementRattachement(UniteRecherche $unite): array
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er");
        $qb
            ->addSelect("e, s")
            ->join('er.etablissement', 'e')
            ->join('e.structure', 's')
            ->join('er.unite', 'ur')->addSelect('ur')
            ->join('ur.structure', 'ur_structure')->addSelect('ur_structure')
            ->andWhereStructureIs($unite->getStructure(), 'ur_structure')
            ->orderBy('s.libelle');

        return $qb->getQuery()->getResult();
    }

    public function existEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement): ?EtablissementRattachement
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er");
        $qb
            ->join('er.unite', 'ur')->addSelect('ur')
            ->join('er.etablissement', 'etab')->addSelect('etab')
            ->join('ur.structure', 'ur_structure')->addSelect('ur_structure')
            ->join('etab.structure', 'etab_structure')->addSelect('etab_structure')
            ->andWhereStructureIs($unite->getStructure(), 'ur_structure')
            ->andWhereStructureIs($etablissement->getStructure(), 'etab_structure')
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : Plusieurs EtablissementRattachement trouvés pour une ED et un Etab donnés");
        }
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

    public function create(UniteRecherche $structureConcrete, Utilisateur $createur): UniteRecherche
    {
        try {
            /** @var TypeStructure $typeStructure */
            $typeStructure = $this->entityManager->getRepository(TypeStructure::class)->findOneBy(['code' => TypeStructure::CODE_UNITE_RECHERCHE]);
        } catch (NotSupported $e) {
            throw new RuntimeException("Erreur lors de l'obtention du repository Doctrine", null, $e);
        }

        $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        $structureConcrete->setSourceCode($sourceCode);
        $structureConcrete->setHistoCreateur($createur);

        $structure = $structureConcrete->getStructure();
        $structure->setTypeStructure($typeStructure);
        $structure->setSourceCode($sourceCode);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($structure);
            $this->entityManager->persist($structureConcrete);
            $this->entityManager->flush($structure);
            $this->entityManager->flush($structureConcrete);
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw new RuntimeException(
                "Erreur lors de l'enregistrement de la structure '$structure' (type : '$typeStructure')", null, $e);
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
        $unite->getStructure()->setCheminLogo($cheminLogo);
        $this->flush($unite);

        return $unite;
    }

    public function deleteLogo(UniteRecherche $unite)
    {
        $unite->getStructure()->setCheminLogo(null);
        $this->flush($unite);

        return $unite;
    }

    private function flush(UniteRecherche $ur)
    {
        try {
            $this->getEntityManager()->flush($ur);
            $this->getEntityManager()->flush($ur->getStructure());
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }

    /** ETABLISSEMENT DE RATTACHEMENT **/

    /**
     * @param UniteRecherche $unite
     * @param Etablissement  $etablissement
     */
    public function addEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $er = new EtablissementRattachement();
        $er->setUniteRecherche($unite);
        $er->setEtablissement($etablissement);
        try {
            $this->getEntityManager()->persist($er);
            $this->getEntityManager()->flush($er);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }

    /**
     * @param UniteRecherche $unite
     * @param Etablissement  $etablissement
     */
    public function removeEtablissementRattachement(UniteRecherche $unite, Etablissement $etablissement)
    {
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er")
            ->andWhere("er.unite = :unite")
            ->andWhere("er.etablissement = :etablissement")
            ->setParameter("unite", $unite)
            ->setParameter("etablissement", $etablissement);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }

        if ($result) {
            try {
                $this->getEntityManager()->remove($result);
                $this->getEntityManager()->flush($result);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
            }
        }
    }
}