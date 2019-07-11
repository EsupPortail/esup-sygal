<?php

namespace Information\Controller;

use Application\Controller\Plugin\Uploader\UploaderPlugin;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\VersionFichier;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Information\Form\FichierForm;
use Information\Service\InformationFichierServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
    use FichierServiceAwareTrait;

    public function indexAction()
    {
        /** @var FichierForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(FichierForm::class);
        $form->setAttribute('action', $this->url()->fromRoute("informations/fichiers", [], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $file = $request->getFiles()->toArray();
            if ($file['chemin'] !== null && $file['chemin']['tmp_name'] !== "") {

                $this->fichierService->createFichiersFromUpload(
                    $uploadData = ['files' => $file['chemin']],
                    NatureFichier::CODE_DIVERS,
                    VersionFichier::CODE_ORIG
                );

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
        $contenuFichier = $this->fichierService->fetchContenuFichier($fichier);
        $fichier->setContenuFichierData($contenuFichier);

        // Envoi du fichier au client (navigateur)
        // NB: $fichier doit être de type \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
        $fichier->setNom($fichier->getNomOriginal());
        $this->uploader()->download($fichier);
    }

}