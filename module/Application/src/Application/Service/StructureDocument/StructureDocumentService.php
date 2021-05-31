<?php

namespace Application\Service\StructureDocument;

use Application\Entity\DateTimeAwareTrait;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureDocument;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class StructureDocumentService {
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
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

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository(StructureDocument::class)->createQueryBuilder('document')
            ->addSelect('nature')->join('document.nature', 'nature')
            ->addSelect('structure')->join('document.structure', 'structure')
            ->addSelect('etablissement')->leftjoin('document.etablissement', 'etablissement')
            ->addSelect('fichier')->leftJoin('document.fichier', 'fichier')
        ;
        return $qb;
    }

    /**
     * @return StructureDocument[]]
     */
    public function getStructuresDocuments() : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.histoDestruction IS NULL');
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Structure $structure
     * @return StructureDocument[]]
     */
    public function getStructuresDocumentsByStructure(Structure  $structure) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.histoDestruction IS NULL')
            ->andWhere('document.structure = :structure')
            ->setParameter('structure', $structure);
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int|null $id
     * @return StructureDocument|null
     */
    public function getStructureDocument(?int $id) : ?StructureDocument
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
        $result = $this->getStructureDocument($id);
        return $result;
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

    public function getContenus(Structure $structure)
    {
        $documents = $this->getStructuresDocumentsByStructure($structure);
        $contenus = [];
        foreach ($documents as $document) {
            $contenus[$document->getId()] = $this->fichierService->fetchContenuFichier($document->getFichier());
        }
        return $contenus;
    }

    public function getContenu(Structure $structure, string $nature_code) {
        $documents = $this->getStructuresDocumentsByStructure($structure);
        foreach ($documents as $document) {
            if ($document->getNature()->getCode() === $nature_code) return $this->fichierService->fetchContenuFichier($document->getFichier());
        }
        return null;
    }

}