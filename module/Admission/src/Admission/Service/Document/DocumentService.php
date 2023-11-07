<?php

namespace Admission\Service\Document;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Document;
use Admission\Entity\Db\Repository\DocumentRepository;
use Application\Entity\DateTimeAwareTrait;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Util;

class DocumentService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DateTimeAwareTrait;

    /**
     * @return DocumentRepository
     */
    public function getRepository(): DocumentRepository
    {
        /** @var DocumentRepository $repo */
        $repo = $this->entityManager->getRepository(Document::class);

        return $repo;
    }

    /** Gestion des entités *******************************************************************************************/

    /**
     * @param Document $document
     * @return Document
     */
    public function create(Document $document) : Document
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
     * @param Document $document
     * @return Document
     */
    public function update(Document $document) : Document
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
     * @param Document $document
     * @return Document
     */
    public function historise(Document $document) : Document
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
     * @param Document $document
     * @return Document
     */
    public function restore(Document $document) : Document
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
     * @param Document $document
     * @return Document
     */
    public function delete(Document $document) : Document
    {
        try {
            $this->getEntityManager()->remove($document);
            $this->getEntityManager()->flush($document);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la suppression d\'un document lié à une structure', $e);
        }

        return $document;
    }

    /** REQUETAGE ****************************************************************************************************
     * @throws NotSupported
     */

    public function createQueryBuilder() : DefaultQueryBuilder
    {
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->getEntityManager()->getRepository(Document::class)->createQueryBuilder('document')
            ->addSelect('nature')->join('document.nature', 'nature')
            ->addSelect('admission')->join('document.admission', 'admission')
            ->addSelect('fichier')->leftJoin('document.fichier', 'fichier');

        return $qb;
    }

    /**
     * @return Document[]]
     * @throws NotSupported
     */
    public function getAdmissionDocuments() : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.histoDestruction IS NULL');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Admission $admission
     * @return Document[]]
     * @throws NotSupported
     */
    public function getAdmissionDocumentsByAdmission(Admission $admission) : array
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.histoDestruction IS NULL')
            ->andWhere($admission, 'admission');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return Document|null
     */
    public function getDocument(int $id) : ?Document
    {
        $qb = $this->createQueryBuilder()
            ->andWhere('document.id = :id')
            ->setParameter('id', $id);
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Document partagent le même id [".$id."]");
        }

        return $result;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Document|null
     */
    public function getRequestedDocument(AbstractActionController $controller, string $param = 'document') : ?Document
    {
        $id = $controller->params()->fromRoute($param);

        return $this->getDocument($id);
    }

    /** USAGE *********************************************************************************************************/

    /**
     * @param Admission $admission
     * @param NatureFichier $nature
     * @param Fichier $fichier
     * @return Document
     */
    public function addDocument(Admission $admission, NatureFichier $nature, Fichier $fichier) : Document
    {
        $document = new Document();
        $document->setAdmission($admission);
        $document->setNature($nature);
        $document->setFichier($fichier);

        $this->create($document);
        return $document;
    }

    /**
     * @throws NotSupported
     */
    public function getContenusFichiers(Admission $admission): array
    {
        $documents = $this->getAdmissionDocumentsByAdmission($admission);
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
     * @param Admission $admission
     * @param string $nature_code
     * @return Fichier|null
     */
    public function findDocumentFichierForAdmissionNature(Admission $admission, string $nature_code): ?Fichier
    {
        $documents = $this->getAdmissionDocumentsByAdmission($admission);
        foreach ($documents as $document) {
            if ($document->getNature()->getCode() === $nature_code) {
                return $document->getFichier();
            }
        }

        return null;
    }

    /**
     * @param Admission $admission
     * @param string $nature_code
     * @return string|null
     * @throws StorageAdapterException
     */
    public function getCheminFichier(Admission $admission, string $nature_code): ?string
    {
        $fichier = $this->findDocumentFichierForAdmissionNature($admission, $nature_code);
        if ($fichier === null) {
            return null;
        }

        $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);

        return $this->fichierStorageService->getFileForFichier($fichier);
    }
}