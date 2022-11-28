<?php

namespace Structure\Service\StructureDocument;

use Application\Entity\DateTimeAwareTrait;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Mvc\Controller\AbstractActionController;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureDocument;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Util;

class StructureDocumentService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DateTimeAwareTrait;

    /** Gestion des entités *******************************************************************************************/

    /**
     * @param StructureDocument $document
     * @return StructureDocument
     */
    public function create(StructureDocument $document) : StructureDocument
    {
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $document->setHistoCreation($date);
        $document->setHistoCreateur($user);
        $document->setHistoModification($date);
        $document->setHistoModificateur($user);

        try {
            $this->getEntityManager()->persist($document);
            $this->getEntityManager()->flush($document);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la création d\'un document lié à une structure', $e);
        }

        return $document;
    }

    /**
     * @param StructureDocument $document
     * @return StructureDocument
     */
    public function update(StructureDocument $document) : StructureDocument
    {
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $document->setHistoModification($date);
        $document->setHistoModificateur($user);

        try {
            $this->getEntityManager()->flush($document);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la mise à jour d\'un document lié à une structure', $e);
        }

        return $document;
    }

    /**
     * @param StructureDocument $document
     * @return StructureDocument
     */
    public function historise(StructureDocument $document) : StructureDocument
    {
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $document->setHistoDestruction($date);
        $document->setHistoDestructeur($user);

        try {
            $this->getEntityManager()->flush($document);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de l\'historisation d\'un document lié à une structure', $e);
        }

        return $document;
    }

    /**
     * @param StructureDocument $document
     * @return StructureDocument
     */
    public function restore(StructureDocument $document) : StructureDocument
    {
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();

        $document->setHistoModification($date);
        $document->setHistoModificateur($user);

        try {
            $this->getEntityManager()->flush($document);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la restauration d\'un document lié à une structure', $e);
        }

        return $document;
    }

    /**
     * @param StructureDocument $document
     * @return StructureDocument
     */
    public function delete(StructureDocument $document) : StructureDocument
    {
        try {
            $this->getEntityManager()->remove($document);
            $this->getEntityManager()->flush($document);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la suppression d\'un document lié à une structure', $e);
        }

        return $document;
    }

    /** REQUETAGE *****************************************************************************************************/

    public function createQueryBuilder() : DefaultQueryBuilder
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(StructureDocument::class)->createQueryBuilder('document')
            ->addSelect('nature')->join('document.nature', 'nature')
            ->addSelect('structure')->join('document.structure', 'structure')
            ->addSelect('etablissement')->leftjoin('document.etablissement', 'etablissement')
            ->addSelect('fichier')->leftJoin('document.fichier', 'fichier');

        return $qb;
    }

    /**
     * @return StructureDocument[]]
     */
    public function getStructuresDocuments() : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.histoDestruction IS NULL');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Structure $structure
     * @return StructureDocument[]]
     */
    public function getStructuresDocumentsByStructure(Structure $structure) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.histoDestruction IS NULL')
            ->andWhereStructureOuSubstituanteIs($structure, 'structure');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return StructureDocument|null
     */
    public function getStructureDocument(int $id) : ?StructureDocument
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.id = :id')
            ->setParameter('id', $id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs StructureDocument partagent le même id [".$id."]");
        }

        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return StructureDocument|null
     */
    public function getRequestedStructureDocument(AbstractActionController $controller, string $param = 'document') : ?StructureDocument
    {
        $id = $controller->params()->fromRoute($param);

        return $this->getStructureDocument($id);
    }

    /** USAGE *********************************************************************************************************/

    /**
     * @param Structure $structure
     * @param Etablissement|null $etablissement
     * @param NatureFichier $nature
     * @param Fichier $fichier
     * @return StructureDocument
     */
    public function addDocument(Structure $structure, ?Etablissement $etablissement, NatureFichier $nature, Fichier $fichier) : StructureDocument
    {
        $document = new StructureDocument();
        $document->setStructure($structure);
        $document->setEtablissement($etablissement);
        $document->setNature($nature);
        $document->setFichier($fichier);

        $this->create($document);
        return $document;
    }

    public function getContenusFichiers(Structure $structure): array
    {
        $documents = $this->getStructuresDocumentsByStructure($structure);
        $contenus = [];
        foreach ($documents as $document) {
            try {
                $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
                $contenus[$document->getId()] = $this->fichierStorageService->getFileContentForFichier($document->getFichier());
            } catch (StorageAdapterException $e) {
                $contenus[$document->getId()] = Util::createImageWithText(implode('|', str_split($e->getMessage(), 25)), 200, 200);
            }
        }
        return $contenus;
    }

    /**
     * @param \Structure\Entity\Db\Structure $structure
     * @param string $nature_code
     * @param \Structure\Entity\Db\Etablissement $etablissement
     * @return \Fichier\Entity\Db\Fichier|null
     */
    public function findDocumentFichierForStructureNatureAndEtablissement(Structure     $structure,
                                                                          string        $nature_code,
                                                                          Etablissement $etablissement): ?Fichier
    {
        $documents = $this->getStructuresDocumentsByStructure($structure);
        foreach ($documents as $document) {
            if ($document->getNature()->getCode() === $nature_code && $document->getEtablissement() === $etablissement) {
                return $document->getFichier();
            }
        }

        return null;
    }

    /**
     * @param Structure $structure
     * @param string $nature_code
     * @param Etablissement|null $etablissement
     * @return string|null
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    public function getCheminFichier(Structure $structure, string $nature_code, ?Etablissement $etablissement = null): ?string
    {
        $fichier = $this->findDocumentFichierForStructureNatureAndEtablissement($structure, $nature_code, $etablissement);
        if ($fichier === null) {
            return null;
        }

        $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);

        return $this->fichierStorageService->getFileForFichier($fichier);
    }
}