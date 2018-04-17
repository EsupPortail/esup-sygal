<?php

namespace Application\Service\Structure;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\StructureSubstit;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\BaseService;
use Application\Service\Source\SourceService;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 * @author Unicaen
 */
class StructureService extends BaseService
{
    use SourceServiceAwareTrait;

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(Structure::class);

        return $repo;
    }

    /**
     * Enregistre en bdd la substitution de plusieurs structures par une autre structure.
     * NB: la structure de subsitition est créée et sa source est SYGAL.
     *
     * @param StructureConcreteInterface[] $structuresSources
     * Structures à substituer (Etablissement|EcoleDoctorale|UniteRecherche
     * @param StructureConcreteInterface   $structureCibleDataObject
     * Objet contenant les attributs de la structure de substitution à créer
     * @return StructureConcreteInterface Entités créées (une par substitution)
     */
    public function createStructureSubstitutions(array $structuresSources, StructureConcreteInterface $structureCibleDataObject)
    {
        // todo: à améliorer si besoin de vérifier que toutes les structures à substituer sont de la même classe
        Assert::allIsInstanceOfAny($structuresSources, [
            Etablissement::class,
            EcoleDoctorale::class,
            UniteRecherche::class,
        ]);

        Assert::null($structureCibleDataObject->getSourceCode(), "Le source code doit être null car il est calculé");

        // le source code d'une structure cible est calculé
        $sourceCode = uniqid(Etablissement::CODE_COMUE . Etablissement::ETAB_PREFIX_SEP);

        // la source d'une structure cible est forcément SYGAL
        $sourceSygal = $this->sourceService->fetchSourceSygal();

        // le type de la structure cible dépend du type des données spécifiées (data object)
        $tsCode = null;
        if ($structureCibleDataObject instanceof Etablissement) {
            $tsCode = TypeStructure::CODE_ETABLISSEMENT;
        } elseif ($structureCibleDataObject instanceof EcoleDoctorale) {
            $tsCode = TypeStructure::CODE_ECOLE_DOCTORALE;
        } elseif ($structureCibleDataObject instanceof UniteRecherche) {
            $tsCode = TypeStructure::CODE_UNITE_RECHERCHE;
        }
        $typeStructure = $this->fetchTypeStructure($tsCode);

        $structureCibleDataObject->setSourceCode($sourceCode);

        // instanciation du couple (Etab|ED|UR ; Structure) cible
        $structureConcreteCible = Structure::constructFromDataObject($structureCibleDataObject, $typeStructure, $sourceSygal);
        $structureRattachCible = $structureConcreteCible->getStructure(); // StructureSubstitution ne référence que des entités de type Structure

        // instanciations des substitutions
        $substitutions = StructureSubstit::fromStructures($structuresSources, $structureRattachCible);

        // enregistrement en bdd
        $this->getEntityManager()->beginTransaction();
        try {
            $this->getEntityManager()->persist($structureConcreteCible);
            $this->getEntityManager()->persist($structureRattachCible);
            array_map(function(StructureSubstit $ss) {
                $this->getEntityManager()->persist($ss);
            }, $substitutions);

            $this->getEntityManager()->flush($structureConcreteCible);
            $this->getEntityManager()->flush($structureRattachCible);
            $this->getEntityManager()->flush($substitutions);

            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement des substitutions", null, $e);
        }

        return $structureConcreteCible;
    }

    /**
     * Met à jour en bdd la substitution existante de plusieurs structures par une autre structure.
     *
     * @param StructureConcreteInterface[] $structuresSources
     * Structures à substituer (Etablissement|EcoleDoctorale|UniteRecherche
     * @param Structure                  $structureCible Structure de subsitution existante
     */
    public function updateStructureSubstitutions(array $structuresSources, Structure $structureCible)
    {
        Assert::notEmpty($structuresSources, "La liste des structures à substituer ne peut être vide");

        Assert::notNull($structureCible->getId(), "La structure de substitution doit exister en bdd");
        Assert::eq(
            $code = SourceInterface::CODE_SYGAL,
            $structureCible->getSource()->getCode(),
            "La source de la structure de substitution doit être $code");

        // todo: à améliorer si besoin de vérifier que toutes les structures à substituer sont de la même classe
        Assert::allIsInstanceOfAny($structuresSources, [
            Etablissement::class,
            EcoleDoctorale::class,
            UniteRecherche::class,
        ]);

        // recherche des substitutions existantes
        $structureSubstitsExistantes =
            $this->getEntityManager()->getRepository(StructureSubstit::class)->findBy(['toStructure' => $structureCible]);

        // détermination des substitutions à créer et à supprimer
        $structureSubstitsExistantesParStructure = [];
        $structuresSourcesExistantes = [];
        /** @var StructureSubstit[] $structureSubstitsExistantes */
        foreach ($structureSubstitsExistantes as $ss) {
            $structureSource = $ss->getFromStructure();
            $structuresSourcesExistantes[] = $structureSource;
            $structureSubstitsExistantesParStructure[$structureSource->getId()] = $ss;
        }
        /** @var Structure[] $structuresSourcesToAdd */
        $structuresSourcesToAdd = array_diff($structuresSources, $structuresSourcesExistantes);
        /** @var Structure[] $structuresSourcesToRem */
        $structuresSourcesToRem = array_diff($structuresSourcesExistantes, $structuresSources);

        // enregistrement en bdd
        $structureSubstits = [];
        $this->getEntityManager()->beginTransaction();
        try {
            foreach ($structuresSourcesToAdd as $structureSource) {
                $ss = StructureSubstit::fromStructures([$structureSource], $structureCible)[0];
                $this->getEntityManager()->persist($ss);
                $structureSubstits[] = $ss;
            }
            foreach ($structuresSourcesToRem as $structureSource) {
                $ss = $structureSubstitsExistantesParStructure[$structureSource->getId()];
                $this->getEntityManager()->remove($ss);
                $structureSubstits[] = $ss;
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        } catch(\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement des substitutions", null, $e);
        }
    }

    /**
     * Suppression en bdd des substitutions pointant vers la structure spécifiée.
     *
     * @param Structure $structureCible Structure de substitution (cible)
     * @return StructureSubstit[] Substitutions supprimées
     */
    public function deleteStructureSubstitutions(Structure $structureCible)
    {
        Assert::notNull($structureCible->getId(), "La structure de substitution doit exister en bdd");
        Assert::eq(
            $code = SourceInterface::CODE_SYGAL,
            $structureCible->getSource()->getCode(),
            "La source de la structure de substitution doit être $code");

        // recherche des substitutions existantes
        $structureSubstits =
            $this->getEntityManager()->getRepository(StructureSubstit::class)->findBy(['toStructure' => $structureCible]);

        Assert::notEmpty($structureSubstits, "Aucune substitution trouvée pour la structure cible '$structureCible'");

        // enregistrement en bdd
        $this->getEntityManager()->beginTransaction();
        try {
            foreach ($structureSubstits as $ss) {
                $this->getEntityManager()->remove($ss);
            }
            $this->getEntityManager()->flush($structureSubstits);
            $this->getEntityManager()->commit();
        } catch(\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de la supression des substitutions", null, $e);
        }

        return $structureSubstits;
    }

    /**
     * Fetch un type de structure à partir de son code.
     *
     * @param string $code Ex: TypeStructure::CODE_ECOLE_DOCTORALE
     * @return TypeStructure
     */
    public function fetchTypeStructure($code)
    {
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => $code]);

        return $typeStructure;
    }

    /**
     * @param  int $idCible
     * @return Structure
     */
    public function findStructureSubsitutionCibleById($idCible)
    {
        $qb = $this->getRepository()->createQueryBuilder("s")
            ->addSelect("ss")
            ->join("s.structuresSubstituees", "ss")
            ->andWhere("s.id = :idCible")
            ->setParameter("idCible", $idCible);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie plusieurs structure cible trouvée.", 0, $e);
        }
        return $result;
    }


    /**
     * Retourne la structure concrete associé à une structure
     * @param Structure $cible
     * @return StructureConcreteInterface
     */
    public function findStructureConcreteFromStructure(Structure $cible)
    {
        $repo = null;
        switch(true) {
            case $cible->getTypeStructure()->isEtablissement() :
                $repo = $this->getEntityManager()->getRepository(Etablissement::class);
                break;
            case $cible->getTypeStructure()->isEcoleDoctorale() :
                $repo = $this->getEntityManager()->getRepository(EcoleDoctorale::class);
                break;
            case $cible->getTypeStructure()->isUniteRecherche() :
                $repo = $this->getEntityManager()->getRepository(UniteRecherche::class);
                break;
            default :
                throw new RuntimeException("TypeStructure non reconnu [".$cible->getTypeStructure()."] .");

        }
        $qb = $repo->createQueryBuilder("sc")
            ->andWhere("sc.structure =  :structure")
            ->setParameter("structure", $cible);
        try {
            $structureConcrete = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie plusieurs structure cible trouvée.", 0, $e);
        }
        return $structureConcrete;
    }

    /**
     * Retourne la liste des structures substituées (i.e. structures cibles)
     * @return Structure[]
     */
    public function getStructuresSubstituees()
    {
        $qb = $this->getEntityManager()->getRepository(Structure::class)->createQueryBuilder("s")
            ->andWhere("s.structuresSubstituees IS NOT EMPTY");

        $results = $qb->getQuery()->getResult();
        return $results;
    }


    /**
     * Retourne la structure ayant un id donné
     * @param $idCible
     * @return Structure
     */
    public function findStructureById($idCible)
    {
        $result = $this->getRepository()->findOneBy(["id" => $idCible]);
        return $result;
    }

    /**
     * Détruit les substitutions associées à une structure cible dans la table STRUCTURE_SUBSTIT et détruit cette structure cible
     * @param StructureConcreteInterface $cibleConcrete
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeSubstitution(StructureConcreteInterface $cibleConcrete)
    {
        $qb = $this->getEntityManager()->getRepository(StructureSubstit::class)->createQueryBuilder("ss")
            ->andWhere("ss.toStructure = :cible")
            ->setParameter("cible", $cibleConcrete->getStructure());
        $result = $qb->getQuery()->getResult();

        foreach($result as $entry) {
            $this->getEntityManager()->remove($entry);
        }
        $this->getEntityManager()->flush($result);

        $this->getEntityManager()->remove($cibleConcrete);
        $this->getEntityManager()->flush($cibleConcrete);
    }

    /**
     * @param  TypeStructure $typeStructure
     * @return StructureConcreteInterface|null
     */
    public function createStructureConcrete($typeStructure) {
        $structureCibleDataObject = null;
        switch($typeStructure) {
            case TypeStructure::CODE_ETABLISSEMENT :
                $structureCibleDataObject = new Etablissement();
                $structureCibleDataObject->setCode(uniqid());
                break;
            case TypeStructure::CODE_ECOLE_DOCTORALE :
                $structureCibleDataObject = new EcoleDoctorale();
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $structureCibleDataObject = new UniteRecherche();
                break;
            default:
                throw new RuntimeException("Type de structure inconnu [".$typeStructure."]");
        }
        return $structureCibleDataObject;
    }


    public function updateFromPostData($structure, $data)
    {
        $hydrator = new DoctrineObject($this->getEntityManager());
        $hydrator->hydrate($data, $structure);
    }

    public function getStructuresConcretesByType($typeStructure) {
        $structures = [];
        switch($typeStructure) {
            case TypeStructure::CODE_ETABLISSEMENT :
                $structures = $this->getEntityManager()->getRepository(Etablissement::class)->findAll();
                break;
            case TypeStructure::CODE_ECOLE_DOCTORALE :
                $structures = $this->getEntityManager()->getRepository(EcoleDoctorale::class)->findAll();
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $structures = $this->getEntityManager()->getRepository(UniteRecherche::class)->findAll();
                break;
            default:
                throw new RuntimeException("Type de structure inconnu [".$typeStructure."]");
        }

        usort($structures, function ($a,$b) { return $a->getLibelle() > $b->getLibelle();});
        return $structures;
    }
}