<?php

namespace Structure\Service\ComposanteEnseignement;

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
use Structure\Entity\Db\Repository\ComposanteEnseignementRepository;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\ComposanteEnseignement;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method ComposanteEnseignement|null findOneBy(array $criteria, array $orderBy = null)
 */
class ComposanteEnseignementService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    /**
     * @return ComposanteEnseignementRepository
     */
    public function getRepository(): ComposanteEnseignementRepository
    {
        /** @var ComposanteEnseignementRepository $repo */
        $repo = $this->entityManager->getRepository(ComposanteEnseignement::class);

        return $repo;
    }

    /**
     * @param ComposanteEnseignement $unite
     * @return EtablissementRattachement[]
     */
    public function findEtablissementRattachement(ComposanteEnseignement $unite): array
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er");
        $qb
            ->addSelect("e, s")
            ->join('er.etablissement', 'e')
            ->join('e.structure', 's')
            ->join('er.unite', 'ur')->addSelect('ur')
            ->join('ur.structure', 'ur_structure')->addSelect('ur_structure')
            ->andWhereStructureOuSubstituanteIs($unite->getStructure(), 'ur_structure')
            ->andWhereStructureEstNonSubstituee('ur_structure')
            ->orderBy('s.libelle');

        return $qb->getQuery()->getResult();
    }

    public function existEtablissementRattachement(ComposanteEnseignement $unite, Etablissement $etablissement): ?EtablissementRattachement
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(EtablissementRattachement::class)->createQueryBuilder("er");
        $qb
            ->join('er.unite', 'ur')->addSelect('ur')
            ->join('er.etablissement', 'etab')->addSelect('etab')
            ->join('ur.structure', 'ur_structure')->addSelect('ur_structure')
            ->join('etab.structure', 'etab_structure')->addSelect('etab_structure')
            ->andWhereStructureOuSubstituanteIs($unite->getStructure(), 'ur_structure')
            ->andWhereStructureOuSubstituanteIs($etablissement->getStructure(), 'etab_structure')
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
     * @param ComposanteEnseignement $ur
     * @param Utilisateur    $destructeur
     */
    public function deleteSoftly(ComposanteEnseignement $ur, Utilisateur $destructeur)
    {
        $ur->historiser($destructeur);
        $ur->getStructure()->historiser($destructeur);

        $this->flush($ur);
    }

    public function undelete(ComposanteEnseignement $ur)
    {
        $ur->dehistoriser();
        $ur->getStructure()->dehistoriser();

        $this->flush($ur);
    }

    public function create(ComposanteEnseignement $structureConcrete, Utilisateur $createur): ComposanteEnseignement
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

    public function update(ComposanteEnseignement $ur)
    {
        $this->flush($ur);

        return $ur;
    }

    public function setLogo(ComposanteEnseignement $unite, $cheminLogo)
    {
        $unite->getStructure()->setCheminLogo($cheminLogo);
        $this->flush($unite);

        return $unite;
    }

    public function deleteLogo(ComposanteEnseignement $unite)
    {
        $unite->getStructure()->setCheminLogo(null);
        $this->flush($unite);

        return $unite;
    }

    private function flush(ComposanteEnseignement $ur)
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
     * @param ComposanteEnseignement $unite
     * @param Etablissement  $etablissement
     */
    public function addEtablissementRattachement(ComposanteEnseignement $unite, Etablissement $etablissement)
    {
        $er = new EtablissementRattachement();
        $er->setComposanteEnseignement($unite);
        $er->setEtablissement($etablissement);
        try {
            $this->getEntityManager()->persist($er);
            $this->getEntityManager()->flush($er);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de l'UR", null, $e);
        }
    }

    /**
     * @param ComposanteEnseignement $unite
     * @param Etablissement  $etablissement
     */
    public function removeEtablissementRattachement(ComposanteEnseignement $unite, Etablissement $etablissement)
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

    //todo faire les filtrage et considerer que les UR internes
    public function getUnitesRecherchesAsOptions() : array
    {
        $unites = $this->getRepository()->findAll();

        $options = [];
        foreach ($unites as $unite) {
            $options[$unite->getId()] = $unite->getStructure()->getLibelle() . " " ."<span class='badge'>".$unite->getStructure()->getSigle()."</span>";
        }
        return $options;
    }
}