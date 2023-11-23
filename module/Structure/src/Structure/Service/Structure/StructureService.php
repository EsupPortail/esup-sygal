<?php

namespace Structure\Service\Structure;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

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
            ->join("s.substitues", "substituees");

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
            foreach ($structureSubstituante->getSubstitues() as $structure) {
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
            $structure instanceof StructureConcreteInterface ? $structure->getStructure() : $structure
        );
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
     * Recherche de structures concrètes par leur type.
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

        // NB : il faut faire explicitement ces jointures sinon Doctrine génère à chaque ligne 3 select pour chacune des 3 relations
        // 'one-to-one' (structure-->etablissement, structure-->ecoleDoctorale, structure-->uniteRecherche), ce qui multiplie
        // le nombre de requêtes par 3 !
        $qb->addSelect('se')->leftJoin('structure.etablissement', 'se');
        $qb->addSelect('sed')->leftJoin('structure.ecoleDoctorale', 'sed');
        $qb->addSelect('sur')->leftJoin('structure.uniteRecherche', 'sur');

        $qb->addSelect('substitues')->leftJoin('structureConcrete.substitues', 'substitues');

        $cacheable = $cacheable && getenv('APPLICATION_ENV') === 'production';
        $qb->getQuery()->setCacheable($cacheable);
        if ($cacheable) {
            $qb->getQuery()->setCacheRegion(__METHOD__ . '_' . $type . '_' . $order);
        }

        return $qb->getQuery()->useQueryCache($cacheable)->enableResultCache($cacheable)->getResult();
    }

    /**
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
            ->addSelect('source')->join('structureConcrete.source', 'source')
            ->andWhereNotHistorise('structureConcrete');

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
            ->setParameter('structureId', $structureId);

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