<?php

namespace Admission\Service\Document;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Document;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Repository\DocumentRepository;
use Admission\Entity\Db\Verification;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Entity\DateTimeAwareTrait;
use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\ORMException;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class DocumentService extends BaseService
{
    use UserContextServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DateTimeAwareTrait;
    use VerificationServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

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
        try {
            $this->getEntityManager()->persist($document);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Inscription");
        }

        return $document;
    }

    /**
     * @param Admission $admission
     * @param Fichier[] $fichiers
     * @return Document
     */
    public function createDocumentFromUpload(Admission $admission, array $fichiers) : Document
    {
        $fichier = array_pop($fichiers); // il n'y a qu'un fichier
        $document = new Document();
        $document->setAdmission($admission);
        $document->setFichier($fichier);

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
            $verification = $document->getVerificationDocument()->first();
            if($verification instanceof Verification){
                $this->verificationService->delete($verification);
            }
            $this->getEntityManager()->remove($document);
            $this->getEntityManager()->flush($document);
            $fichier = $document->getFichier();
            if($fichier instanceof Fichier){
                $this->fichierService->supprimerFichiers([$fichier]);
            }
        } catch (ORMException $e) {
            throw new RuntimeException('Un problème est survenu lors de la suppression d\'un document', $e);
        }

        return $document;
    }

    /**
     * Supprime en bdd tous les documents sur un dossier d'admission.
     *
     * @param Admission $admission
     */
    public function deleteAllDocumentsForAdmission(Admission $admission)
    {
        try {
            foreach ($admission->getDocument() as $document) {
                $this->delete($document);
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
        }
    }

    public function getPathFile(Fichier $fichier){
        return $this->fichierStorageService->getFileForFichier($fichier);
    }

    public function getExtensionDocument(Fichier $fichier){
        $pathParts = pathinfo($fichier->getNomOriginal());

        return mb_strtolower($pathParts['extension']);
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

    public function createDocumentWithoutFichier(Admission $admission){
        /** @var Document $document */
        $document = $this->getRepository()->findOneWhereNoFichierByAdmission($admission)[0] ?? null;

        //on en crée un Fieldset Document sans fichier
        //afin de relier une Vérification à celui-ci
        if (!$document instanceof Document) {
            try {
                $document = new Document();
                $document->setAdmission($admission);
                $this->create($document);
            } catch (\Exception $e) {
                throw new RuntimeException("Impossible d'enregistrer ce document", $e);
            }
        }
    }

    public function addCharteDoctoraleToAdmission(Inscription $inscription)
    {
        try {
            /** @var Admission $admission */
            $admission = $inscription->getAdmission();
            $etablissement = $inscription?->getEtablissementInscription();

            /** @var Fichier $charteDoctorat */
            $charteDoctorat = $etablissement ? $this->structureDocumentService->findDocumentFichierForStructureNatureAndEtablissement(
                $etablissement->getStructure(),
                NatureFichier::CODE_ADMISSION_CHARTE_DOCTORAT,
                $etablissement) : null;

            if ($charteDoctorat) {
                $nature = $this->getRepository()->fetchNatureFichier(NatureFichier::CODE_ADMISSION_CHARTE_DOCTORAT);
                $charteDoctoratPath = $this->getPathFile($charteDoctorat);
                $extension = $this->getExtensionDocument($charteDoctorat);

                $newCharteDoctorat = new Fichier();
                $newCharteDoctorat->setPath($charteDoctoratPath);
                $newCharteDoctorat->setNomOriginal($charteDoctorat->getNomOriginal());
                $newCharteDoctorat->setTypeMime($charteDoctorat->getTypeMime());
                $newCharteDoctorat->setTaille($charteDoctorat->getTaille());
                $newCharteDoctorat->setNature($nature);
                $newCharteDoctorat->setVersion($charteDoctorat->getVersion());
                $newCharteDoctorat->setNom(NatureFichier::CODE_ADMISSION_CHARTE_DOCTORAT . "-" . $newCharteDoctorat->getShortUuid() . "." . $extension);

                $this->createDocumentFromUpload($admission, [$newCharteDoctorat]);
            }
        } catch (\Exception $die) {
            return $die->getMessage();
        }
    }
}