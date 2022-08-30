<?php

namespace Information\Controller;

use Fichier\Controller\Plugin\Uploader\UploaderPlugin;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Information\Form\FichierForm;
use Information\Service\InformationFichierServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 *
 *
 * @method UploaderPlugin uploader()
 *
 * @author Unicaen
 */
class FichierController extends AbstractActionController
{
    use InformationFichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;

    /**
     * @var FichierForm
     */
    private $fichierForm;

    /**
     * @param FichierForm $fichierForm
     */
    public function setFichierForm(FichierForm $fichierForm)
    {
        $this->fichierForm = $fichierForm;
    }

    public function indexAction()
    {
        /** @var FichierForm $form */
        $form = $this->fichierForm;
        $form->setAttribute('action', $this->url()->fromRoute("informations/fichiers", [], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $file = $request->getFiles()->toArray();
            if ($file['chemin'] !== null && $file['chemin']['tmp_name'] !== "") {

                $fichiers = $this->fichierService->createFichiersFromUpload(
                    $uploadData = ['files' => $file['chemin']],
                    NatureFichier::CODE_COMMUNS,
                    VersionFichier::CODE_ORIG
                );
                $this->fichierService->saveFichiers($fichiers);

                return $this->redirect()->toRoute("informations/fichiers");
            }
        }

        $fichiers = $this->getInformationFichierService()->getInformationFichiers();

        return new ViewModel([
            'fichiers' => $fichiers,
            'form'     => $form,
        ]);
    }

    public function supprimerAction()
    {
        /** @var Fichier $fichier */
        $id = $this->params()->fromRoute('id');
        $fichier = $this->getInformationFichierService()->getInformationFichier($id);

        $this->fichierService->supprimerFichiers([$fichier]);

        return $this->redirect()->toRoute('informations/fichiers', [], [], true);
    }

    public function telechargerAction()
    {
        /** @var Fichier $fichier */
        $id = $this->params()->fromRoute('id');
        $fichier = $this->getInformationFichierService()->getInformationFichier($id);
        if (!$fichier) {
            throw new RuntimeException("Fichier introuvable avec cet id");
        }

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du Fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        // Envoi du fichier au client (navigateur)
        // NB: $fichier doit être de type \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
        $fichier->setNom($fichier->getNomOriginal());
        $this->uploader()->download($fichier);
    }

}