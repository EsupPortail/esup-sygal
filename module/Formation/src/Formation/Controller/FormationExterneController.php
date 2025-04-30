<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\RouteMatch;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Formation\Entity\Db\FormationExterne;
use Formation\Form\Formation\FormationExterne\FormationExterneFormAwareTrait;
use Formation\Provider\Template\MailTemplates;
use Formation\Provider\Validation\TypeValidation;
use Formation\Service\Formation\FormationExterne\FormationExterneServiceAwareTrait;
use Formation\Service\Notification\FormationNotificationFactoryAwareTrait;
use Formation\Service\Validation\ValidationFormationExterne\ValidationFormationExterneServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationExterneController extends AbstractController
{
    use EntityManagerAwareTrait;
    use FormationExterneFormAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use FormationExterneServiceAwareTrait;
    use ValidationFormationExterneServiceAwareTrait;
    use FormationNotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;

    public function ajouterAction() : ViewModel
    {
        /** @var Doctorant $doctorant */
        $doctorantId = $this->params()->fromRoute('doctorant');
        if ($doctorantId !== null) {
            $doctorant = $this->getEntityManager()->getRepository(Doctorant::class)->find($doctorantId);
        } else {
            $user = $this->userContextService->getIdentityDb();
            $doctorant = $this->doctorantService->getDoctorantsByUser($user);
        }

        $formationExterne = new FormationExterne();
        $formationExterne->setDoctorant($doctorant);

        $form = $this->getFormationExterneForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/ajouter-formation-externe', ['doctorant' => $doctorant->getId()], [], true));
        $form->bind($formationExterne);

        $request = $this->getRequest();
        if ($request->isPost()) {
            //permet d'envoyer aussi l'attestation dans la vérification du formulaire
            $form->setData(array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            ));
            if ($form->isValid()) {
                $files = $request->getFiles()->toArray();
                if(isset($files['attestation']['size']) && $files['attestation']['size'] !== 0){
                    /** @var NatureFichier $nature */
                    $nature = $this->natureFichierService->getRepository()->findOneBy(['code' => \Formation\Provider\NatureFichier\NatureFichier::CODE_ATTESTATION_FORMATION_EXTERIEURE]);
                    $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
                    $this->fichierService->saveFichiers($fichiers);
                    $formationExterne->setAttestation(array_pop($fichiers));
                }
                $this->formationExterneService->create($formationExterne);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une formation externe",
            'form' => $form,
            'urlFichierPlugin' => $this->urlFichier(),
        ]);
        $vm->setTemplate('formation/formation-externe/modifier');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);

        $form = $this->getFormationExterneForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/modifier-formation-externe', [], [], true));
        $form->bind($formationExterne);
        $form->getInputFilter()->get('attestation')->setRequired(false);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $files = $request->getFiles()->toArray();
            $files = isset($files['attestation']['size']) && $files['attestation']['size'] !== 0 ? $files : [];
            //permet d'envoyer aussi l'attestation dans la vérification du formulaire
            $form->setData(array_merge_recursive(
                $request->getPost()->toArray(),
                $files
            ));
            if ($form->isValid()) {
                /** @var NatureFichier $nature */
                $nature = $this->natureFichierService->getRepository()->findOneBy(['code' => \Formation\Provider\NatureFichier\NatureFichier::CODE_ATTESTATION_FORMATION_EXTERIEURE]);

                if(isset($files['attestation']['size']) && $files['attestation']['size'] !== 0){
                    //Suppression de l'ancienne attestation, si une déjà présente
                    if($formationExterne->getAttestation()) $formationExterne->setAttestation(null);
                    $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
                    $this->fichierService->saveFichiers($fichiers);
                    $formationExterne->setAttestation(array_pop($fichiers));
                }

                $this->formationExterneService->update($formationExterne);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification de la formation externe ".$formationExterne->getLibelle(),
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation-externe/modifier');
        return $vm;
    }

    public function historiserAction()
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);
        $this->formationExterneService->historise($formationExterne);

        $retour = $this->params()->fromQuery('redirect');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
    }

    public function restaurerAction()
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);
        $this->formationExterneService->restore($formationExterne);

        $retour = $this->params()->fromQuery('redirect');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
    }

    public function supprimerAction()
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->formationExterneService->delete($formationExterne);
            exit();
        }

        $vm = new ViewModel();
        if ($formationExterne !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression d'une formation externe #" . $formationExterne->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formation/supprimer-formation-externe', ["formationExterne" => $formationExterne->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function telechargerAttestationAction(): void
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);
        $fichier = $formationExterne->getAttestation();

        if (!$fichier) {
            return;
        }

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        // Envoi du fichier au client (navigateur)
        $this->uploader()->download($fichier);
    }

    public function supprimerAttestationAction(): Response|ViewModel
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();
        $fichierId = $routeMatch->getParam("fichier");
        $fichier = $this->fichierService->getRepository()->find($fichierId);

        $result = $this->confirm()->execute();

        // si un tableau est retourné par le plugin Confirm, l'opération a été confirmée
        if (is_array($result)) {
            $formationExterne->setAttestation(null);
            $this->formationExterneService->update($formationExterne);

            $this->fichierService->supprimerFichiers([$fichier]);

            $this->flashMessenger()->addSuccessMessage("L'attestation de suivi de la formation {$formationExterne->getLibelle()} supprimée avec succès.");
            if($redirectUrl = $this->params()->fromQuery('redirect')){
                return $this->redirect()->toUrl($redirectUrl);
            }
        }

        $viewModel = $this->confirm()->getViewModel();
        $viewModel->setTemplate("fichier/fichier/supprimer");
        $viewModel->setVariables([
            'title'   => "Suppression d'un fichier",
            'fichier' => $fichier,
        ]);

        return $viewModel;
    }

    public function validerFormationExterneAction()
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);
        $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
        $this->validationFormationExterneService->create(TypeValidation::CODE_VALIDATION_FORMATION_EXTERNE, $formationExterne, $individuUtilisateur);

        if($redirectUrl = $this->params()->fromQuery('redirect')){
            return $this->redirect()->toUrl($redirectUrl);
        }
    }

    public function refuserFormationExterneAction()
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);

        $individuUtilisateur = $this->userContextService->getIdentityDb()->getIndividu();
        $this->validationFormationExterneService->create(TypeValidation::CODE_REFUS_FORMATION_EXTERNE, $formationExterne, $individuUtilisateur);

        if($redirectUrl = $this->params()->fromQuery('redirect')){
            return $this->redirect()->toUrl($redirectUrl);
        }
    }

    public function revoquerFormationExterneAction()
    {
        $formationExterne = $this->formationExterneService->getRepository()->getRequestedFormationExterne($this);

        $validation = $this->validationFormationExterneService->getRepository()->findValidationByFormationExterneAndCode($formationExterne,TypeValidation::CODE_VALIDATION_FORMATION_EXTERNE);
        if($validation) $this->validationFormationExterneService->historiser($validation);

        $refus = $this->validationFormationExterneService->getRepository()->findValidationByFormationExterneAndCode($formationExterne,TypeValidation::CODE_REFUS_FORMATION_EXTERNE);
        if($refus) $this->validationFormationExterneService->historiser($refus);

        $this->flashMessenger()->addSuccessMessage("La validation concernant cette formation a bien été révoquée.");

        if($redirectUrl = $this->params()->fromQuery('redirect')){
            return $this->redirect()->toUrl($redirectUrl);
        }
    }

    public function notifierFormationExterneAjouteeAction(): Response
    {
        $these = $this->requestedThese();
        try {
            $notif = $this->formationNotificationFactory->createNotificationFormationExterneAjoutee($these);
            if (empty($notif->getTo())) {
                throw new RuntimeException(
                    "Aucun mail pour la notification [" . MailTemplates::FORMATION_EXTERNE_AJOUTEE . "]");
            }
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire , todo : cas à gérer !
        }

        $this->flashMessenger()->addSuccessMessage("Notification envoyée au gestionnaire");

        if($redirectUrl = $this->params()->fromQuery('redirect')){
            return $this->redirect()->toUrl($redirectUrl);
        }
    }
}