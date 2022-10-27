<?php

namespace Structure\Service\Structure;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Repository\EcoleDoctoraleRepository;
use Structure\Entity\Db\Repository\EtablissementRepository;
use Structure\Entity\Db\Repository\UniteRechercheRepository;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\StructureInterface;
use Structure\Entity\Db\StructureSubstit;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use Webmozart\Assert\Assert;

/**
 * @author Unicaen
 */
class StructureService extends BaseService
{
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use FichierStorageServiceAwareTrait;

    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
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
    public function createStructureSubstitutions(array $structuresSources, StructureConcreteInterface $structureCibleDataObject): StructureConcreteInterface
    {
        switch (true) {
            case ($structuresSources[0] instanceOf Etablissement):
                Assert::allIsInstanceOf($structuresSources, Etablissement::class);
                break;
            case ($structuresSources[0] instanceOf EcoleDoctorale):
                Assert::allIsInstanceOf($structuresSources, EcoleDoctorale::class);
                break;
            case ($structuresSources[0] instanceOf UniteRecherche):
                Assert::allIsInstanceOf($structuresSources, UniteRecherche::class);
                break;
            default:
                throw new RuntimeException("La première structure est de type non connu.");
        }

        // le source code d'une structure cible est calculé
        $sourceCode = $structureCibleDataObject->getSourceCode();
        if ($sourceCode === null) {
            $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        }

        // la source d'une structure cible est forcément SYGAL
        $sourceSygal = $this->sourceService->fetchApplicationSource();

        // le type de la structure cible dépend du type des données spécifiées (data object)
        $tsCode = null;
        if ($structureCibleDataObject instanceof Etablissement) {
            $tsCode = TypeStructure::CODE_ETABLISSEMENT;
        } elseif ($structureCibleDataObject instanceof EcoleDoctorale) {
            $tsCode = TypeStructure::CODE_ECOLE_DOCTORALE;
        } elseif ($structureCibleDataObject instanceof UniteRecherche) {
            $tsCode = TypeStructure::CODE_UNITE_RECHERCHE;
        }
        $typeStructure = $this->findOneTypeStructureForCode($tsCode);

        $structureCibleDataObject->setSourceCode($sourceCode);

        // si toutes les structures sources ont le même code alors on le réutilise, sinon on en génère un unique
        $codeExtractor = function($structureSource) {
            /** @var StructureConcreteInterface $structureSource */
            return $this->sourceCodeStringHelper->removePrefixFrom($structureSource->getSourceCode());
        };
        $codesStructuresSources = array_map($codeExtractor, $structuresSources);
        if (count(array_unique($codesStructuresSources)) === 1) {
            $codeStructureCible = reset($codesStructuresSources);
        } else {
            $codeStructureCible = uniqid();
        }

        // instanciation du couple (Etab|ED|UR ; Structure) cible
        $structureConcreteCibleSourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo($codeStructureCible);
        $structureConcreteCible = Structure::constructFromDataObject($structureCibleDataObject, $typeStructure, $sourceSygal);
        $structureConcreteCible->setSourceCode($structureConcreteCibleSourceCode);
        $structureConcreteCible->getStructure()->setSourceCode($structureConcreteCibleSourceCode);
        $structureConcreteCible->getStructure()->setCode($codeStructureCible);
        $structureRattachCible = $structureConcreteCible->getStructure(); // StructureSubstitution ne référence que des entités de type Structure

        // instanciations des substitutions
        $substitutions = StructureSubstit::fromStructures($structuresSources, $structureRattachCible);

        // enregistrement en bdd
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($structureRattachCible);
            $this->entityManager->persist($structureConcreteCible);
            array_map(function(StructureSubstit $ss) {
                $this->entityManager->persist($ss);
            }, $substitutions);

            $this->entityManager->flush($structureRattachCible);
            $this->entityManager->flush($structureConcreteCible);
            $this->entityManager->flush($substitutions);

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
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
        $source = $this->sourceService->fetchApplicationSource();

        Assert::notEmpty($structuresSources, "La liste des structures à substituer ne peut être vide");

        Assert::notNull($structureCible->getId(), "La structure de substitution doit exister en bdd");
        Assert::eq(
            $code = $source->getCode(),
            $structureCible->getSource()->getCode(),
            "La source de la structure de substitution doit être '$code'.");

        switch (true) {
            case ($structuresSources[0] instanceOf Etablissement):
                Assert::allIsInstanceOf($structuresSources, Etablissement::class);
                break;
            case ($structuresSources[0] instanceOf EcoleDoctorale):
                Assert::allIsInstanceOf($structuresSources, EcoleDoctorale::class);
                break;
            case ($structuresSources[0] instanceOf UniteRecherche):
                Assert::allIsInstanceOf($structuresSources, UniteRecherche::class);
                break;
            default:
                throw new RuntimeException("La première structure est de type non connu.");
        }

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
        } catch(Exception $e) {
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
    public function deleteStructureSubstitutions(Structure $structureCible): array
    {
        $source = $this->sourceService->fetchApplicationSource();

        Assert::notNull($structureCible->getId(), "La structure de substitution doit exister en bdd");
        Assert::eq(
            $code = $source->getCode(),
            $structureCible->getSource()->getCode(),
            "La source de la structure de substitution doit être '$code'.");

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
        } catch(Exception $e) {
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
    public function findOneTypeStructureForCode(string $code): TypeStructure
    {
        /** @var TypeStructure $typeStructure */
        $typeStructure = $this->getEntityManager()->getRepository(TypeStructure::class)->findOneBy(['code' => $code]);

        return $typeStructure;
    }

    /**
     * @param  int $idCible
     * @return Structure
     */
    public function findStructureSubsitutionCibleById($idCible): ?Structure
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
     * Retourne la structure concrète (Etablissement, EcoleDoctorale, ou UniteRecherche) correspondant
     * à une Structure "abstraite".
     *
     * @param Structure $cible
     * @return StructureConcreteInterface|null
     */
    public function findStructureConcreteFromStructure(Structure $cible): ?StructureConcreteInterface
    {
        /** @var EcoleDoctoraleRepository|EtablissementRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($cible->getTypeStructure()->getCode()));

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
     * Cette fonction retourne la liste des structures qui substituent d'autres structures.
     *
     * @return Structure[]
     */
    public function findStructuresSubstituantes(string $codeType = null, string $order = null): array
    {
        $qb = $this->getEntityManager()->getRepository(Structure::class)->createQueryBuilder("s")
            ->addSelect('substituees')
            ->join("s.structuresSubstituees", "substituees");

        if ($codeType) {
            $qb
                ->join('s.typeStructure', 'ts', Join::WITH, 'ts.code = :code')
                ->setParameter("code", $codeType);
        }
        if ($order) {
            $qb->orderBy('s.' . $order);
        }

        return $qb->getQuery()->getResult();
    }

    public function findStructuresSubstituees(): array
    {
        $structuresSubstituantes = $this->findStructuresSubstituantes();

        $dictionnaire = [];
        foreach($structuresSubstituantes as $structureSubstituante) {
            foreach ($structureSubstituante->getStructuresSubstituees() as $structure) {
                $dictionnaire[$structure->getId()] = $structure;
            }
        }

        $result = [];
        foreach ($dictionnaire as $structure) {
            $result[] = $structure;
        }

        return $result;
    }

    /**
     * Retourne la structure ayant un id donné
     * @param $idCible
     * @return Structure|null
     */
    public function findStructureById($idCible): ?Structure
    {
        /** @var Structure|null $result */
        $result = $this->getRepository()->findOneBy(["id" => $idCible]);

        return $result;
    }

    /**
     * Détruit les substitutions associées à une structure cible dans la table STRUCTURE_SUBSTIT et détruit cette structure cible.
     *
     * @param StructureConcreteInterface $cibleConcrete
     */
    public function removeSubstitution(StructureConcreteInterface $cibleConcrete)
    {
        $qb = $this->getEntityManager()->getRepository(StructureSubstit::class)->createQueryBuilder("ss")
            ->andWhere("ss.toStructure = :cible")
            ->setParameter("cible", $cibleConcrete->getStructure());

        $this->getEntityManager()->beginTransaction();
        try {
            foreach($qb->getQuery()->getResult() as $entry) {
                $this->getEntityManager()->remove($entry);
            }
            $this->getEntityManager()->remove($cibleConcrete);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        } catch (ORMException $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Problème rencontré lors de la suppression de la substitution de structures");
        }
    }

    /**
     * @param string $typeStructure
     * @return StructureConcreteInterface|null
     */
    public function createStructureConcrete(string $typeStructure): ?StructureConcreteInterface
    {
        $sourceSygal = $this->sourceService->fetchApplicationSource();
        $type = $this->findOneTypeStructureForCode($typeStructure);

        switch($typeStructure) {
            case TypeStructure::CODE_ETABLISSEMENT :
                $structureCibleDataObject = new Etablissement();
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
        $sourceCode = $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
        $structureCibleDataObject->getStructure()->setSourceCode($sourceCode);
        $structureCibleDataObject->getStructure()->setTypeStructure($type);
        $structureCibleDataObject->setSource($sourceSygal);
        $structureCibleDataObject->getStructure()->setSource($sourceSygal);

        return $structureCibleDataObject;
    }

    /**
     * @param \Structure\Entity\Db\StructureInterface|\Structure\Entity\Db\StructureConcreteInterface $structure
     * @param array $data
     */
    public function updateFromPostData($structure, array $data)
    {
        $hydrator = new DoctrineObject($this->getEntityManager());
        $hydrator->hydrate(
            $data,
            $structure instanceof StructureConcreteInterface ? $structure->getStructure(false) : $structure
        );
    }

    /**
     * @param ?string $typeStructure
     * @return StructureConcreteInterface[]
     */
    public function findStructuresConcretes(string $typeStructure = null): array
    {
        if ($typeStructure === null) {
            $ecoles = $this->findStructuresConcretes(TypeStructure::CODE_ECOLE_DOCTORALE);
            $etablissements = $this->findStructuresConcretes(TypeStructure::CODE_ETABLISSEMENT);
            $unites = $this->findStructuresConcretes(TypeStructure::CODE_UNITE_RECHERCHE);

            return array_merge($ecoles, $etablissements, $unites);
        }

        /** @var EcoleDoctoraleRepository|EtablissementRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($typeStructure));

        $qb = $repo->createQueryBuilder("s")
            ->leftJoin("s.structure", "str")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->andWhereStructureEstNonSubstituee('str')
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        return $qb->getQuery()->getResult();
    }

    public function findStructuresBySuffixeAndType(string $identifiant, string $typeStructure): array
    {
        /** @var EcoleDoctoraleRepository|EtablissementRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($typeStructure));

        $qb = $repo->createQueryBuilder("structureConcrete")
            ->andWhere("structureConcrete.sourceCode LIKE :criteria")
            ->setParameter("criteria", "%::".$identifiant)
            ->andWhereStructureEstNonSubstituee('structure');

        return $qb->getQuery()->getResult();
    }

    protected function getEntityClassForType(string $type): string
    {
        switch($type) {
            case TypeStructure::CODE_ECOLE_DOCTORALE :
            case 'École doctorale':
                return EcoleDoctorale::class;
            case TypeStructure::CODE_UNITE_RECHERCHE :
            case 'Unité de recherche':
                return UniteRecherche::class;
            case TypeStructure::CODE_ETABLISSEMENT :
            case 'Établissement':
                return Etablissement::class;
            default :
                throw new RuntimeException('Type de structure inconnu ['.$type.']');
        }
    }

    /**
     * Retourne les structures qui peuvent être substituées, **hydratées au format array**.
     *
     * @param string $type
     * @return array[]
     */
    public function findStructuresSubstituablesByType(string $type): array
    {
        /** @var EcoleDoctoraleRepository|EtablissementRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($type));

        $qb = $repo->createQueryBuilder('structureConcrete')
            ->leftJoin('structure.structuresSubstituees', 'structuresSubstituees')
            ->andWhereStructureEstNonSubstituante()
            //->andWhereStructureEstNonSubstituee()
            ->orderBy('structure.libelle');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Les structures non substituées.
     *
     * @param string $type Ex: {@see TypeStructure::CODE_ECOLE_DOCTORALE}
     * @param string|null $order Ex: 'libelle'
     * @param bool $includeFermees
     * @param bool $cacheable
     * @return StructureConcreteInterface[]
     */
    public function findAllStructuresAffichablesByType(string $type, ?string $order = null, ?bool $includeFermees = true, bool $cacheable = false): array
    {
        $qb = $this->findAllStructuresAffichablesByTypeQb($type, $order, $includeFermees);

        $cacheable = $cacheable && getenv('APPLICATION_ENV') === 'production';
        $qb->getQuery()->setCacheable($cacheable);
        if ($cacheable) {
            $qb->getQuery()->setCacheRegion(__METHOD__ . '_' . $type . '_' . $order);
        }

        return $qb->getQuery()->useQueryCache($cacheable)->enableResultCache($cacheable)->getResult();
    }

    /**
     * Query builder pour Les structures non substituées.
     *
     * @param string $type Ex: {@see TypeStructure::CODE_ECOLE_DOCTORALE}
     * @param string|null $order Ex: 'libelle'
     * @param bool $includeFermees
     * @return QueryBuilder
     */
    public function findAllStructuresAffichablesByTypeQb(string $type, string $order = null, bool $includeFermees = true): QueryBuilder
    {
        /** @var EtablissementRepository|EcoleDoctoraleRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($type));

        $qb = $repo->createQueryBuilder('structureConcrete')
            ->leftJoin('structure.structuresSubstituees', 'substitutionFrom')->addSelect('substitutionFrom')
            ->andWhereStructureEstNonSubstituee();

        if ($order) {
            $qb->orderBy('structure.estFermee , structure.' . $order);
        }
        else {
            if ($type === TypeStructure::CODE_ECOLE_DOCTORALE || $type === TypeStructure::CODE_UNITE_RECHERCHE) {
                $qb->orderBy('structure.estFermee, structureConcrete.sourceCode');
            }
        }

        if (! $includeFermees) {
            $qb->andWhere('structure.estFermee = false');
        }

        return $qb;
    }

    /**
     * @param string $type
     * @param int|string $structureId
     * @return StructureConcreteInterface
     */
    public function findStructureConcreteByTypeAndStructureId(string $type, $structureId): StructureConcreteInterface
    {
        /** @var EtablissementRepository|EcoleDoctoraleRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($type));

        $qb = $repo->createQueryBuilder('structureConcrete')
            ->addSelect('structurec')
            ->join('structureConcrete.structure', 'structurec')
            ->andWhere('structurec.id = :structureId')
            ->setParameter('structureId', $structureId);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs ".$type." partagent le même identifiant de structure [".$structureId."]");
        }
        if (!$result) throw new RuntimeException("Aucun(e) ".$type." de trouvé(e).");

        return $result;
    }

    /**
     * @param string $type
     * @param int|string $structureId
     * @return StructureConcreteInterface
     */
    public function findStructureConcreteByTypeAndStructureConcreteId(string $type, $structureId): StructureConcreteInterface
    {
        /** @var EtablissementRepository|EcoleDoctoraleRepository|UniteRechercheRepository $repo */
        $repo = $this->getEntityManager()->getRepository($this->getEntityClassForType($type));

        $qb = $repo->createQueryBuilder('structureConcrete')
            ->addSelect('structurec')
            ->join('structureConcrete.structure', 'structurec')
            ->andWhere('structureConcrete.id = :structureId')
            ->setParameter('structureId', $structureId)
            ->andWhereStructureEstNonSubstituee();

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs ".$type." partagent le même identifiant de structure [".$structureId."]");
        }

        if (!$result) {
            throw new RuntimeException("Aucun(e) ".$type." trouvé(e).");
        }

        return $result;
    }

    /**
     * Identifie les structures substituables en comparant les sourceCode sans le prefixe.
     *
     * @param string $type
     * @return array [$identifiant => [$sources, $cible]]
     */
    public function findStructuresSubstituablesSelonSourceCode(string $type): array
    {
        $structures = [];
        switch($type) {
            case (TypeStructure::CODE_ECOLE_DOCTORALE):
                $structures = $this->getEcoleDoctoraleService()->getRepository()->findSubstituables();
                break;
            case (TypeStructure::CODE_ETABLISSEMENT):
                $structures = $this->getEtablissementService()->getRepository()->findSubstituables();
                break;
            case (TypeStructure::CODE_UNITE_RECHERCHE):
                $structures = $this->getUniteRechercheService()->getRepository()->findSubstituables();
                break;
        }

        $dictionnaire = [];
        foreach ($structures as $structure) {
            try {
                $identifiant = $this->sourceCodeStringHelper->removePrefixFrom($structure->getSourceCode());
            } catch (Exception $e) {
                $identifiant = $structure->getSourceCode();
            }
            if ($identifiant) {
                $dictionnaire[$identifiant][] = $structure;
            }
        }

        $substitutions = [];
        foreach ($dictionnaire as $identifiant => $structures) {
            if (count($structures) >= 2) {
                $cible = null;
                $substitutions[$identifiant] = [$structures, $cible];
            }
        }

        return $substitutions;
    }

    public function getSubstitutionDictionnary(string $identifiant, string $typeStructure): array
    {
        $structures = $this->findStructuresBySuffixeAndType($identifiant, $typeStructure);
        $cible = null;

        return [
            "cible" => $cible,
            "sources" => $structures
        ];
    }

    /**
     * Supprime le logo d'une structure.
     *
     * @param StructureInterface $structure
     * @return bool
     */
    public function deleteLogoStructure(StructureInterface $structure): bool
    {
        if (!$structure->getCheminLogo()) {
            return false;
        }

        try {
            $this->fichierStorageService->deleteFileForLogoStructure($structure);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Erreur lors de la suppression du logo de la structure. " . $e->getMessage(), null, $e);
        }

        $structure->setCheminLogo(null);
        try {
            $this->entityManager->flush($structure);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la structure.", null, $e);
        }

        return true;
    }

    /**
     * Met à jour le logo d'une structure.
     *
     * @param StructureInterface $structure
     * @param string             $uploadedFilePath
     */
    public function updateLogoStructure(StructureInterface $structure, string $uploadedFilePath)
    {
        if ($uploadedFilePath === '') {
            throw new RuntimeException("Chemin du fichier logo uploadé invalide.");
        }

        $logoFilepath = FileUtils::convertLogoFileToPNG($uploadedFilePath);

        // Suppression du logo existant
        $this->entityManager->beginTransaction();
        try {
            $this->deleteLogoStructure($structure);

            $this->fichierStorageService->saveFileForLogoStructure($logoFilepath, $structure);

            $logoFilename = $this->fichierStorageService->computeFileNameForNewLogoStructure($structure);
            $structure->setCheminLogo($logoFilename);

            $this->entityManager->flush($structure);
            $this->entityManager->commit();
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'enregistrer le fichier logo dans le storage. " . $e->getMessage(), null, $e);
        } catch (ORMException $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur lors de l'enregistrement de la structure. " . $e->getMessage(), null, $e);
        }
    }

    /**
     * Retourne au format chaîne de caractères le contenu du logo de la structure spécifiée.
     *
     * @param \Structure\Entity\Db\StructureInterface|null $structure
     * @return string|null
     */
    public function getLogoStructureContent(StructureInterface $structure = null): ?string
    {
        if ($structure === null OR $structure->getCheminLogo() === null) {
            return Util::createImageWithText("Aucun logo|renseigné", 200, 200);
        }

        try {
            $logoFilepath = $this->fichierStorageService->getFileForLogoStructure($structure);
        } catch (StorageAdapterException $e) {
            return Util::createImageWithText("Anomalie: Fichier|absent sur le storage. " . $e->getMessage(), 200, 200);
        }

        if ($logoFilepath === null) {
            return null;
        }

        return file_get_contents($logoFilepath) ?: null;
    }

    /**
     * @param string $code
     * @return TypeStructure
     */
    public function getTypeStructureByCode(string $code): TypeStructure
    {
        $qb = $this->getEntityManager()->getRepository(TypeStructure::class)->createQueryBuilder('type')
            ->andWhere('type.code = :code')
            ->setParameter('code', $code)
            ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Un problème s'est produit", $e);
        }

        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Structure
     */
    public function getRequestedStructure(AbstractActionController $controller, string $param = 'structure'): Structure
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Structure $structure */
        $structure = $this->getRepository()->find($id);

        return $structure;
    }

    public function getStructuresFormationsAsOptions(): array
    {
        $ecoles = $this->getEcoleDoctoraleService()->getRepository()->findAll();
        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions();
        //$unites = $this->getUnitesRechercheService()->getRepository()->findAll(true);
        $structures = array_merge($ecoles, $etablissements);
        $array = [];
        foreach ($structures as $structure) {
            if (!$structure->getStructure()->estFermee()) {
                $array[$structure->getStructure()->getId()] = (($structure->getStructure()->getTypeStructure())?$structure->getStructure()->getTypeStructure()->getLibelle():"Non précisé"). " - ".$structure->getStructure()->getSigle(). " - " .$structure->getStructure()->getLibelle();
            }
        }
        return $array;
    }
}