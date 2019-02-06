<?php

namespace Information\Controller;

use Application\Entity\Db\Fichier;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Information\Entity\Db\InformationFichier;
use Information\Form\FichierForm;
use Information\Service\InformationFichierServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class FichierController extends AbstractActionController
{
    use FichierServiceAwareTrait;
    use InformationFichierServiceAwareTrait;
    use FileServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function indexAction()
    {
        $fichiers = $this->getInformationFichierService()->getInformationFichiers();
        /** @var FichierForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(FichierForm::class);
        $form->setAttribute('action', $this->url()->fromRoute("informations/fichiers", [], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $file = $request->getFiles()->toArray();
            if ($file['chemin'] !== null && $file['chemin']['tmp_name'] !== "") {
                $pathDir = $this->fileService->computeDirectoryPathForInformation();
                $this->fileService->createWritableDirectory($pathDir);

                $newFilename = $this->getInformationFichierService()->generateUniqueFilename();



                $uploadPath = $file['chemin']['tmp_name'];
                $truePath = implode("/", [$pathDir, $newFilename]);
                $ok = rename($uploadPath, $truePath);
                if (! $ok) {
                    throw new RuntimeException("Impossible de renommer le fichier sur le disque.");
                }

                $user = $this->userContextService->getIdentityDb();
                $fichier = new InformationFichier();
                $fichier->setNom($file['chemin']['name']);
                $fichier->setFilename($newFilename);
                $fichier->setCreateur($user);
                $fichier->setDateCreation(new \DateTime());
                $this->getInformationFichierService()->create($fichier);

                $this->redirect()->toRoute("informations/fichiers");
            }
        }

        return new ViewModel([
            'fichiers' => $fichiers,
            'form' => $form,
        ]);
    }

    public function supprimerAction()
    {
        /** @var InformationFichier $fichier */
        $id = $this->params()->fromRoute('id');
        $fichier = $this->getInformationFichierService()->getInformationFichier($id);
        $this->getInformationFichierService()->delete($fichier);

        $filePath = $this->getInformationFichierService()->computeFilePath($fichier);
        $success = unlink($filePath);
        if (!$success) throw new RuntimeException("Un problème s'est produit lors de l'effacement sur le disque du fichier ".$filePath);

        $this->redirect()->toRoute('informations/fichiers', [], [], true);
    }

    /** TELECHARGEMENT ************************************************************************************************/

    /** Action permettant le téléchargement */
    public function telechargerAction()
    {
        /** @var InformationFichier $fichier */
        $id = $this->params()->fromRoute('id');
        $fichier = $this->getInformationFichierService()->getInformationFichier($id);

        $filePath = $filePath = $this->getInformationFichierService()->computeFilePath($fichier);
        $contenuFichier = file_get_contents($filePath);


        $contentType = 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename=' . $fichier->getNom());
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($contenuFichier));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        echo $contenuFichier;
        exit;
    }

}