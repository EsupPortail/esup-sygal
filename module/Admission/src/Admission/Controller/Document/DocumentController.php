<?php

namespace Admission\Controller\Document;

use Admission\Entity\Db\Document;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Exception;
use Fichier\Service\Fichier\Exception\FichierServiceException;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Validator\File\MimeType;
use Laminas\View\Model\JsonModel;
use UnicaenApp\Exception\RuntimeException;

/**
 * Class DocumentController
 *
 * @method FlashMessenger flashMessenger()
 */
class DocumentController extends AbstractActionController
{
    use AdmissionServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DocumentServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use AdmissionOperationRuleAwareTrait;

    public function enregistrerDocumentAction(): JsonModel|Response|bool
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Récupérez le fichier téléchargé via le gestionnaire de fichiers
            $file = $this->params()->fromFiles();
            foreach($file["document"] as $key=>$fileDetail){
                if (isset($fileDetail["error"]) && $fileDetail["error"] === UPLOAD_ERR_OK) {
                    $natureCode = $this->params()->fromRoute("codeNatureFichier");
                    $nature = $this->documentService->getRepository()->fetchNatureFichier($natureCode);
                    if ($nature === null) {
                        return $this->createErrorResponse(422, "Nature de fichier spécifiée invalide");
                    }
                    $version = $this->versionFichierService->getRepository()->findOneByCode("VO");
                    if ($version === null) {
                        return $this->createErrorResponse(422, "Version de fichier spécifiée invalide");
                    }
                    try {
                        $individu = $this->params()->fromRoute("individu");
                        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);

                        //Vérification de la validité du fichier
                        $fileValidity = $this->isValid($fileDetail);
                        if (!is_bool($fileValidity)) {
                            return $fileValidity;
                        }

                        $fileDetail = ["files" => $fileDetail];
                        $fichier = $this->fichierService->createFichiersFromUpload($fileDetail, $nature, $version);
                        $this->documentService->createDocumentFromUpload($admission, $fichier);
                        return new JsonModel(['success' => 'Document téléversé avec succès']);
                    } catch (Exception $die) {
                        return $this->createErrorResponse(500, $die->getMessage());
                    }
                }
            }
        }
        return false;
    }

    public function supprimerDocumentAction(): JsonModel|Response
    {
        $natureCode = $this->params()->fromRoute("codeNatureFichier");
        $nature = $this->documentService->getRepository()->fetchNatureFichier($natureCode);
        if ($nature === null) {
            return $this->createErrorResponse(422, "Nature de fichier spécifiée invalide");
        }
        try {
            $individu = $this->params()->fromRoute("individu");
            $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);

            /** @var Document $document */
            $document = $this->documentService->getRepository()->findByAdmissionAndNature($admission, $nature);

            $this->documentService->delete($document);
            $this->flashMessenger()->addSuccessMessage("Document justificatif supprimé avec succès.");
            return new JsonModel(['success' => 'Document supprimé avec succès']);
        } catch (Exception $die) {
            return $this->createErrorResponse(500, $die->getMessage());
        }
    }
    public function telechargerDocumentAction(): JsonModel|Response
    {
        $natureCode = $this->params()->fromRoute('codeNatureFichier');
        $nature = $this->documentService->getRepository()->fetchNatureFichier($natureCode);
        if ($nature === null) {
            return $this->createErrorResponse(422, "Nature de fichier spécifiée invalide");
        }
        try {
            $individu = $this->params()->fromRoute('individu');
            $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
            /** @var Document $document */
            $document = $this->documentService->getRepository()->findByAdmissionAndNature($admission, $nature);
            if($document){
                try {
                    $fichierContenu = $this->documentService->recupererDocumentContenu($document);
                } catch (FichierServiceException $e) {
                    throw new RuntimeException("Une erreur est survenue empêchant la création ", null, $e);
                }
                $this->fichierService->telechargerFichier($fichierContenu);
                return new JsonModel(['success' => 'Document téléchargé avec succès']);
            }
        } catch (Exception $die) {
            return $this->createErrorResponse(500, $die->getMessage());
        }
    }
    private function isValid($fileDetail): bool|Response
    {
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];

        $validator = new MimeType($allowedMimeTypes);
        if (!$validator->isValid($fileDetail['tmp_name'])) {
            return $this->createErrorResponse(422, "Le document doit être un PDF, JPG ou PNG");
        }

        $minFileSize = 10 * 1024; // 10 Ko en octets
        $maxFileSize = 4 * 1024 * 1024; // 4 Mo en octets

        $fileSize = filesize($fileDetail['tmp_name']); // Obtient la taille réelle du fichier en octets

        if ($fileSize < $minFileSize || $fileSize > $maxFileSize) {
            return $this->createErrorResponse(422, "Le document ne doit pas excéder 4 Mo");
        }

        return true;
    }
    private function createErrorResponse($status, $message): Response
    {
        $response = new Response();
        $response->setStatusCode($status);
        $response->setContent(json_encode(['errors' => $message]));
        $response->getHeaders()->addHeaders(['Content-Type' => 'application/json']);
        return $response;
    }
}