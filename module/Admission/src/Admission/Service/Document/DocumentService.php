<?php

namespace Admission\Service\Document;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Document;
use Admission\Entity\Db\Repository\DocumentRepository;
use Application\Entity\DateTimeAwareTrait;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Util;

class DocumentService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
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
     * @param Admission $admission
     * @param Fichier $fichier
     * @return Document
     */
    public function createDocumentFromUpload(Admission $admission, array $fichiers) : Document
    {
        $user = $this->userContextService->getIdentityDb();
        $date = $this->getDateTime();
        $fichier = array_pop($fichiers); // il n'y a qu'un fichier
        $document = new Document();
        $document->setAdmission($admission);
        $document->setFichier($fichier);
        $document->setHistoCreation($date);
        $document->setHistoCreateur($user);
        $document->setHistoModification($date);
        $document->setHistoModificateur($user);

        try {
            $this->fichierService->saveFichiers([$fichier]);
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
        $fichier = $document->getFichier();
        try {
            $this->getEntityManager()->remove($document);
            $this->getEntityManager()->flush($document);
            $this->fichierService->supprimerFichiers([$fichier]);
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la suppression d\'un document', $e);
        }

        return $document;
    }



    /**
     * @throws FichierServiceException
     */
    public function recupererDocumentContenu(Document $document): Fichier
    {
        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            $filePath = $this->fichierStorageService->getFileForFichier($document->getFichier());
        } catch (StorageAdapterException $e) {
            $message = "Impossible d'ajouter le fichier suivant : " . $document->getFichier();
            error_log($message);
            throw new FichierServiceException($message, null, $e);
        }

        $fichier = Fichier::fromFilepath($filePath);
        $fichier->setNom($document->getFichier()->getNom());
        return $fichier;
    }
}