<?php

namespace Application\Service\Structure;

use Application\Command\ConvertCommand;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Source;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\StructureInterface;
use Application\Entity\Db\StructureSubstit;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\BaseService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Import\Service\Traits\SynchroServiceAwareTrait;
use Retraitement\Exception\TimedOutCommandException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;
use Webmozart\Assert\Assert;

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
    use FileServiceAwareTrait;

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

        $this->synchroService->addService('these');
        $this->synchroService->synchronize();

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

        $this->synchroService->addService('these');
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

        $this->synchroService->addService('these');
        $this->synchroService->synchronize();
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
            ->andWhere('substitutionTo.id IS NULL OR pasHistorise(substitutionTo) != 1')
            ->orderBy('structure.libelle')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * Les structures non substituées
     *
     * @param string $type
     * @param string $order
     * @return StructureConcreteInterface[]
     */
    public function getAllStructuresAffichablesByType($type, $order = null)
    {
        $qb = $this->getEntityManager()->getRepository($this->getEntityByType($type))->createQueryBuilder('structureConcrete')
            ->addSelect('structure')
            ->addSelect('substitutionTo')
            ->addSelect('substitutionFrom')
            ->join('structureConcrete.structure', 'structure')
            ->leftJoin('structure.structureSubstituante', 'substitutionTo')
            ->leftJoin('structure.structuresSubstituees', 'substitutionFrom')
            ->andWhere('substitutionTo.id IS NULL OR pasHistorise(substitutionTo) != 1');
        if ($order) $qb->orderBy('structure.' . $order);
        else {
            if ($type === TypeStructure::CODE_ECOLE_DOCTORALE) $qb->orderBy('structureConcrete.sourceCode');
        }

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /** Les structures non substituées
     * @param string $type
     * @return StructureConcreteInterface[]
     */
    public function getStructuresSubstitueesUtilisablesByType($type) {
        $qb = $this->getEntityManager()->getRepository($this->getEntityByType($type))->createQueryBuilder('structureConcrete')
            ->addSelect('structure')
            ->addSelect('substitutionTo')
            ->addSelect('substitutionFrom')
            ->join('structureConcrete.structure', 'structure')
            ->leftJoin('structure.structuresSubstituees', 'substitutionFrom')
            ->leftJoin('structure.structureSubstituante', 'substitutionTo')
            ->andWhere('substitutionFrom.id IS NULL')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
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
    public function deleteLogoStructure(StructureInterface $structure)
    {
        $structure->setCheminLogo(null);
        try {
            $this->entityManager->flush($structure);
            if ($structure instanceof StructureConcreteInterface) {
                $this->entityManager->flush($structure->getStructure());
            }
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la structure.", null, $e);
        }

        $logoFilepath = $this->fileService->computeLogoFilePathForStructure($structure);
        if ($fileExists = file_exists($logoFilepath) && $structure->getCheminLogo() !== null) {
            $ok = unlink($logoFilepath);
            if (! $ok) {
                throw new RuntimeException("Impossible de supprimer physiquement le fichier logo sur le disque.");
            }
        }

        return $fileExists;
    }

    /**
     * Met à jour le logo d'une structure.
     *
     * @param StructureInterface $structure
     * @param string             $uploadedFilePath
     */
    public function updateLogoStructure(StructureInterface $structure, string $uploadedFilePath)
    {
        if ($uploadedFilePath === null || $uploadedFilePath === '') {
            throw new RuntimeException("Chemin du fichier logo uploadé invalide.");
        }

        // mise à jour en bdd du chemin vers fichier logo.
        $this->deleteLogoStructure($structure);
        $logoFilename = $this->fileService->computeLogoFileNameForStructure($structure);
        $structure->setCheminLogo($logoFilename);
        try {
            $this->entityManager->flush($structure);
            if ($structure instanceof StructureConcreteInterface) {
                $this->entityManager->flush($structure->getStructure());
            }
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la structure.", null, $e);
        }

        // création du fichier logo sur le disque.
        $logoDir = $this->fileService->computeLogoDirectoryPathForStructure($structure);
        $this->fileService->createWritableDirectory($logoDir);

        $logoFilepath = $this->fileService->computeLogoFilePathForStructure($structure);
        /** ANCIENNE METHODE  */
//        $ok = rename($uploadedFilePath, $logoFilepath);
//        if (! $ok) {
//            throw new RuntimeException("Impossible de renommer le fichier logo sur le disque.");
//        }
        $command = new ConvertCommand();
        $errorFilePath = null;
        $command->generate($logoFilepath, ['logo' => $uploadedFilePath], $errorFilePath);
        try {
            $command->checkResources();
            $command->execute();

            $success = ($command->getReturnCode() === 0);
            if (!$success) {
                throw new RuntimeException(sprintf(
                    "La commande %s a échoué (code retour = %s), voici le résultat d'exécution : %s",
                    $command->getName(),
                    $command->getReturnCode(),
                    implode(PHP_EOL, $command->getResult())
                ));
            }
        } catch (TimedOutCommandException $toce) {
            throw $toce;
        }
        catch (RuntimeException $rte) {
            throw new RuntimeException(
                "Une erreur est survenue lors de l'exécution de la commande " . $command->getName(),
                0,
                $rte);
        }
        //!todo remplacer par 'convert  $uploadedFilePath $logoFilepath'

    }

    /**
     * Retourne au format chaîne de caractères le contenu du logo de la structure spécifiée.
     *
     * @param StructureInterface $structure
     * @return string|null
     */
    public function getLogoStructureContent(StructureInterface $structure = null)
    {
        if ($structure === null OR $structure->getCheminLogo() === null) {
            return Util::createImageWithText("Aucun logo|renseigné", 200, 200);
        }

        $logoFilepath = $this->fileService->computeLogoFilePathForStructure($structure);
        if (! file_exists($logoFilepath)) {
            return Util::createImageWithText("Anomalie: Fichier|absent sur le disque", 200, 200);
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
}