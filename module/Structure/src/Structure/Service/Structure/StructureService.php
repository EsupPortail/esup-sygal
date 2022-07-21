<?php

namespace Structure\Service\Structure;

use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\StructureInterface;
use Structure\Entity\Db\StructureSubstit;
use Application\Entity\Db\These;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Application\Service\BaseService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Fichier\FileUtils;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Import\Service\Traits\SynchroServiceAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use Webmozart\Assert\Assert;
use function Application\generateNameForEtab;

/**
 * @author Unicaen
 */
class StructureService extends BaseService
{
    use SourceServiceAwareTrait;
    use SynchroServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use FichierStorageServiceAwareTrait;

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
                new RuntimeException("La première structure est de type non connu.");
                break;
        }

        //Assert::null($structureCibleDataObject->getSourceCode(), "Le source code doit être null car il est calculé");

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
        $typeStructure = $this->fetchTypeStructure($tsCode);

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
        $this->getEntityManager()->beginTransaction();
        try {
            $this->getEntityManager()->persist($structureRattachCible);
            $this->getEntityManager()->persist($structureConcreteCible);
            array_map(function(StructureSubstit $ss) {
                $this->getEntityManager()->persist($ss);
            }, $substitutions);

            $this->getEntityManager()->flush($structureRattachCible);
            $this->getEntityManager()->flush($structureConcreteCible);
            $this->getEntityManager()->flush($substitutions);

            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement des substitutions", null, $e);
        }

        $this->runSynchroTheses($structureConcreteCible->getStructure());

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
                new RuntimeException("La première structure est de type non connu.");
                break;
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
        } catch(\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement des substitutions", null, $e);
        }

        $this->runSynchroTheses($structureCible);
    }

    /**
     * Lance la synchro des thèses pour prendre en compte la substitution de structure.
     *
     * @param \Structure\Entity\Db\Structure $structureCible
     */
    private function runSynchroTheses(Structure $structureCible)
    {
        // Les noms de synchros sont déclinés par source/établissement (ex: 'these-UCN') ; on ne retient que
        // les sources/établissements des structures substituées.
        $etabs = [];
        foreach ($structureCible->getStructuresSubstituees() as $structuresSubstituee) {
            /** @var \Application\Entity\Db\Source $source */
            $source = $structuresSubstituee->getSource();
            $etab = $source->getEtablissement()->getCode();
            $etabs[$etab] = $etab;
        }
        foreach ($etabs as $etab) {
            $this->synchroService->addService(generateNameForEtab('these-%s', $etab));
        }

        $this->synchroService->synchronize();
    }

    /**
     * Suppression en bdd des substitutions pointant vers la structure spécifiée.
     *
     * @param Structure $structureCible Structure de substitution (cible)
     * @return StructureSubstit[] Substitutions supprimées
     */
    public function deleteStructureSubstitutions(Structure $structureCible)
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
        } catch(\Exception $e) {
            $this->getEntityManager()->rollback();
            throw new RuntimeException("Erreur rencontrée lors de la supression des substitutions", null, $e);
        }

        $this->runSynchroTheses($structureCible);

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
     * Cette fonction retourne la liste des structures qui substitue d'autres structures
     * @return Structure[]
     */
    public function getStructuresSubstituantes($type = null, $order = null)
    {
        $qb = $this->getEntityManager()->getRepository(Structure::class)->createQueryBuilder("s")
            ->addSelect('substituees')
            ->join("s.structuresSubstituees", "substituees")/*
            ->andWhere("s.structuresSubstituees IS NOT EMPTY")*/;
        if ($type) {
            $typeStructure = $this->fetchTypeStructure($type);
            $qb->andWhere("s.typeStructure = :type")
                ->setParameter("type", $typeStructure);
        }
        if ($order)
            $qb->orderBy('s.'.$order);

        $results = $qb->getQuery()->getResult();
        return $results;
    }

    public function getStructuresSubstituees() {
        /** @var Structure[] $structuresSubstituantes */
        $structuresSubstituantes = $this->getStructuresSubstituantes();

        $dictionnaire = [];
        foreach($structuresSubstituantes as $structureSubstituante) {
            foreach ($structureSubstituante->getStructuresSubstituees() as $structure) {
                $dictionnaire[$structure->getId()] = $structure;
            }
        }

        $result = [];
        foreach ($dictionnaire as $key => $structure) {
            $result[] = $structure;
        }
        return $result;
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
        $this->getEntityManager()->remove($cibleConcrete);

        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Problème lors de l'effacement des structures");
        }
    }

    /**
     * @param  TypeStructure $typeStructure
     * @return StructureConcreteInterface|null
     */
    public function createStructureConcrete($typeStructure)
    {
        $sourceSygal = $this->sourceService->fetchApplicationSource();
        $type = $this->fetchTypeStructure($typeStructure);

        $structureCibleDataObject = null;
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
     * @param $structure
     * @param $data
     */
    public function updateFromPostData($structure, $data)
    {
        $hydrator = new DoctrineObject($this->getEntityManager());
        $hydrator->hydrate($data, $structure);
    }

    /**
     * @param TypeStructure $typeStructure
     * @return StructureConcreteInterface[]
     */
    public function getStructuresConcretes($typeStructure = null) {
        $structures = [];

        if ($typeStructure === null) {
            $ecoles = $this->getStructuresConcretes(TypeStructure::CODE_ECOLE_DOCTORALE);
            $etablissements = $this->getStructuresConcretes(TypeStructure::CODE_ETABLISSEMENT);
            $unites = $this->getStructuresConcretes(TypeStructure::CODE_UNITE_RECHERCHE);

            $structures = array_merge($ecoles, $etablissements, $unites);
            return $structures;
        }

        $repo = null;
        switch($typeStructure) {
            case TypeStructure::CODE_ETABLISSEMENT :
                $repo = $this->getEntityManager()->getRepository(Etablissement::class);
                break;
            case TypeStructure::CODE_ECOLE_DOCTORALE :
                $repo = $this->getEntityManager()->getRepository(EcoleDoctorale::class);
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $repo = $this->getEntityManager()->getRepository(UniteRecherche::class);
                break;
            default:
                throw new RuntimeException("Type de structure inconnu [".$typeStructure."]");
        }
        $qb = $repo->createQueryBuilder("s")
            ->leftJoin("s.structure", "str", "WITH", "s.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle")
        ;
        $structures = $qb->getQuery()->getResult();
        return $structures;
    }

    /**
     * Cette fonction retourne un tableau de tableaux contenants des structures concrete ayant le
     * même sourceCode au préfixe près
     * @param TypeStructure $type
     * @return array(StructureConcreteInterface[])
     */
    public function getSubstitutions($type = null)
    {
        $structures = null;
        if ($type === null) $structures = $this->getStructuresConcretes();
        else                $structures = $this->getStructuresConcretes($type);

        $sourceCodeDictionnary = [];
        foreach ($structures as $structure) {
            $sourceCode = $this->sourceCodeStringHelper->removePrefixFrom($structure->getSourceCode());
            $sourceCodeDictionnary[$sourceCode][] = $structure;
        }

        $subsitutions = [];
        foreach ($sourceCodeDictionnary as $key => $collection) {
            if (count($collection) > 1) $subsitutions[] = $collection;
        }

        return $subsitutions;
    }



    /**
     * les structures qui sont subsitutées sont présentent une seule fois dans la table StructureSubstit
     * la récupération de la structure substituante peut passé par une requête de cette table.
     * @param StructureConcreteInterface $structureConcrete
     * @return StructureConcreteInterface|null
     * @throws NonUniqueResultException
     */
    public function findStructureSubstituante(StructureConcreteInterface $structureConcrete)
    {
//        var_dump("from:" . $structureConcrete->getId() . " << ". $structureConcrete->getStructure()->getId());
        $qb = $this->getEntityManager()->getRepository(StructureSubstit::class)->createQueryBuilder("ss")
            ->andWhere("ss.fromStructure = :structure")
            ->setParameter("structure", $structureConcrete->getStructure());
        /** @var StructureSubstit $result */
        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result !== null) {
            $structureCible = $result->getToStructure();
            $structureConcreteCible = $this->findStructureConcreteFromStructure($structureCible);
            return $structureConcreteCible;
        }

        return null;
    }

    public function getStructuresBySuffixe($identifiant, $type)
    {
        $repo = null;
        switch($type) {
            case TypeStructure::CODE_ECOLE_DOCTORALE:
                $repo = $this->getEntityManager()->getRepository(EcoleDoctorale::class);
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE:
                $repo = $this->getEntityManager()->getRepository(UniteRecherche::class);
                break;
            case TypeStructure::CODE_ETABLISSEMENT:
                $repo = $this->getEntityManager()->getRepository(Etablissement::class);
                break;
        }

        $qb = $repo->createQueryBuilder("structureConcrete")
            ->andWhere("structureConcrete.sourceCode LIKE :criteria")
            ->setParameter("criteria", "%::".$identifiant);

        $result = $qb->getQuery()->getResult();

        return $result;
    }


    public function getEntityByType($type) {
        $entity = null;
        switch($type) {
            case TypeStructure::CODE_ECOLE_DOCTORALE :
            case 'École doctorale':
                $entity = EcoleDoctorale::class;
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
            case 'Unité de recherche':
                $entity = UniteRecherche::class;
                break;
            case TypeStructure::CODE_ETABLISSEMENT :
            case 'Établissement':
                $entity = Etablissement::class;
                break;
            default :
                throw new RuntimeException('Type de structure inconnu ['.$type.']');
        }
        return $entity;
    }

    /** Les structures qui peuvent être substituées
     * @param TypeStructure $type
     * @return StructureConcreteInterface[]
     */
    public function getStructuresSubstituablesByType($type)
    {
        $qb = $this->getEntityManager()->getRepository($this->getEntityByType($type))->createQueryBuilder('structureConcrete')
            ->addSelect('structure')
            ->addSelect('substitutionTo')
            ->addSelect('substitutionFrom')
            ->join('structureConcrete.structure', 'structure')
            ->leftJoin('structure.structuresSubstituees', 'substitutionFrom')
            ->leftJoin('structure.structureSubstituante', 'substitutionTo')
            ->andWhere('substitutionFrom.id IS NULL')
            ->andWhere('substitutionTo.id IS NULL OR substitutionTo.histoDestruction is not null')
            ->orderBy('structure.libelle')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * Les structures non substituées.
     *
     * @param string $type Ex: {@see TypeStructure::CODE_ECOLE_DOCTORALE}
     * @param string|null $order Ex: 'libelle'
     * @param bool $includeFermees
     * @param bool $includeFermees
     * @param bool $cacheable
     * @return StructureConcreteInterface[]
     */
    public function getAllStructuresAffichablesByType(string $type, $order = null, $includeFermees = true, $cacheable = false)
    {
        $qb = $this->getAllStructuresAffichablesByTypeQb($type, $order, $includeFermees);

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
    public function getAllStructuresAffichablesByTypeQb(string $type, $order = null, $includeFermees = true)
    {
        $qb = $this->getEntityManager()->getRepository($this->getEntityByType($type))->createQueryBuilder('structureConcrete')
            ->addSelect('structure')
            ->addSelect('substitutionTo')
            ->addSelect('substitutionFrom')
            ->join('structureConcrete.structure', 'structure')
            ->leftJoin('structure.structureSubstituante', 'substitutionTo')
            ->leftJoin('structure.structuresSubstituees', 'substitutionFrom')
            ->andWhere('substitutionTo.id IS NULL OR substitutionTo.histoDestruction is not null');
        if ($order) {
            $qb->orderBy(' structure.estFermee , structure.' . $order);
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
     * @param int $structureId
     * @return StructureConcreteInterface
     */
    public function getStructuresConcreteByTypeAndStructureId($type, $structureId)
    {
        $qb = $this->getEntityManager()->getRepository($this->getEntityByType($type))->createQueryBuilder('structureConcrete')
            ->addSelect('structure')
            ->join('structureConcrete.structure', 'structure')
            ->andWhere('structure.id = :structureId')
            ->setParameter('structureId', $structureId)
        ;

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
     * @param int $structureId
     * @return StructureConcreteInterface
     */
    public function getStructuresConcreteByTypeAndStructureConcreteId($type, $structureId)
    {
        $qb = $this->getEntityManager()->getRepository($this->getEntityByType($type))->createQueryBuilder('structureConcrete')
            ->addSelect('structure')
            ->join('structureConcrete.structure', 'structure')
            ->andWhere('structureConcrete.id = :structureId')
            ->setParameter('structureId', $structureId)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs ".$type." partagent le même identifiant de structure [".$structureId."]");
        }

        if (!$result) throw new RuntimeException("Aucun(e) ".$type." de trouvé(e).");
        return $result;
    }

    /**
     * Identifie les structures substituables en utilisant le sourceCode.
     *
     * @param string $type
     * @return array
     *
     * @deprecated Mise deprecated pour penser à la renommer clairement,
     * et à remplacer if ($prefix === "SyGAL" || $prefix === "COMUE").
     */
    public function checkStructure($type)
    {
        $structures = [];
        switch($type) {
            case (TypeStructure::CODE_ECOLE_DOCTORALE):
                $structures = $this->getEcoleDoctoraleService()->getRepository()->findAll();
                break;
            case (TypeStructure::CODE_ETABLISSEMENT):
                $structures = $this->getEtablissementService()->getRepository()->findAll();
                break;
            case (TypeStructure::CODE_UNITE_RECHERCHE):
                $structures = $this->getUniteRechercheService()->getRepository()->findAll();
                break;
        }

        $dictionnaire = [];
        foreach ($structures as $structure) {
            $identifiant = explode("::", $structure->getSourceCode())[1];
            $dictionnaire[$identifiant][] = $structure;
        }

        $substitutions = [];
        foreach ($dictionnaire as $identifiant => $structures) {
            if (count($structures) >= 2) {
                $sources = [];
                $cible = null;

                /** @var StructureConcreteInterface $structure */
                foreach ($structures as $structure) {
                    $prefix = explode("::",$structure->getSourceCode())[0];
                    if ($prefix === "SyGAL" || $prefix === "COMUE") {
                        $cible = $structure;
                    } else {
                        $sources[] = $structure;
                    }
                }
                $substitutions[$identifiant] = [$sources, $cible];
            }
        }

        return $substitutions;
    }

    /**
     * @deprecated Mise deprecated pour penser à la renommer clairement, la documenter,
     * et à remplacer if ($prefix === "SyGAL" || $prefix === "COMUE").
     */
    public function getSubstitutionDictionnary($identifiant, $type)
    {
        $structures = $this->getStructuresBySuffixe($identifiant, $type);

        $sources = [];
        $cible = null;
        /** @var StructureConcreteInterface $structure */
        foreach ($structures as $structure) {
            $prefix = explode("::",$structure->getSourceCode())[0];
            if ($prefix === "SyGAL" || $prefix === "COMUE") {
                $cible = $structure;
            } else {
                $sources[] = $structure;
            }
        }

        return [
            "cible" => $cible,
            "sources" => $sources
        ];
    }

    public function getUnitesRechercheSelection() {
        $qb = $this->getEntityManager()->getRepository(These::class)->createQueryBuilder('these')
            ->select('count(these.id), unite.id, max(structure.libelle), max(structure.sigle), max(unite.sourceCode)')
            ->leftJoin('these.uniteRecherche', 'unite')
            ->join('unite.structure', 'structure')
            ->leftJoin('structure.structureSubstituante', 'substitutionTo')
            ->andWhere('substitutionTo.id IS NULL')
            ->having('count(these.id) > 0')
            ->groupBy('unite.id')
        ;

        $result = $qb->getQuery()->getResult();

        usort($result, function($a, $b) { return strcmp($a[3], $b[3]);});
        return $result;
    }


    /**
     * Supprime le logo d'une structure.
     *
     * @param StructureInterface $structure
     * @return bool
     */
    public function deleteLogoStructure(StructureInterface $structure): bool
    {
        $cheminLogo = $structure->getCheminLogo();

        $structure->setCheminLogo(null);
        try {
            $this->entityManager->flush($structure);
            if ($structure instanceof StructureConcreteInterface) {
                $this->entityManager->flush($structure->getStructure());
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la structure.", null, $e);
        }
        
        if ($hasLogo = ($cheminLogo !== null)) {
            try {
                $this->fichierStorageService->deleteFileForLogoStructure($structure);
            } catch (StorageAdapterException $e) {
                throw new RuntimeException("Erreur lors de la suppression du logo de la structure. " . $e->getMessage(), null, $e);
            }
        }

        return $hasLogo;
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
        $this->deleteLogoStructure($structure); // todo: améliorer pour l'inclure dns le try-catch ci-après

        try {
            $this->fichierStorageService->saveFileForLogoStructure($logoFilepath, $structure);

            $logoFilename = $this->fichierStorageService->computeFileNameForLogoStructure($structure);
            $structure->setCheminLogo($logoFilename);

            $this->entityManager->flush($structure);
            if ($structure instanceof StructureConcreteInterface) {
                $this->entityManager->flush($structure->getStructure());
            }
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'enregistrer le fichier logo dans le storage. " . $e->getMessage(), null, $e);
        } catch (ORMException $e) {
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

        return file_get_contents($logoFilepath) ?: null;
    }

    /**
     * @param string $code
     * @return TypeStructure
     */
    public function getTypeStructureByCode($code)
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
    public function getRequestedStructure(AbstractActionController $controller, string $param = 'structure')
    {
        $id = $controller->params()->fromRoute($param);
        $structure = $this->getRepository()->find($id);
        return $structure;
    }

    public function getStructuresFormationsAsOptions()
    {
        $ecoles = $this->getEcoleDoctoraleService()->getRepository()->findAll(true);
        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions();
        //$unites = $this->getUnitesRechercheService()->getRepository()->findAll(true);
        $structures = array_merge($ecoles, $etablissements);
        $array = [];
        foreach ($structures as $structure) {
            if (!$structure->getStructure()->estFermee()) {
                $array[$structure->getStructure()->getId()] = (($structure->getStructure()->getTypeStructure())?$structure->getStructure()->getTypeStructure()->getLibelle():"Non précisé"). " - ".$structure->getSigle(). " - " .$structure->getLibelle();
            }
        }
        return $array;
    }
}