<?php
namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Document;
use Admission\Entity\Db\Etat;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\TypeValidation;
use Admission\Entity\Db\Verification;
use Admission\Form\Admission\AdmissionFormAwareTrait;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Provider\Template\MailTemplates;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Etudiant\EtudiantServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Admission\Service\TypeValidation\TypeValidationServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Controller\PaysController;
use Application\Entity\Db\Utilisateur;
use Application\Filter\IdifyFilter;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierServiceException;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\FieldsetInterface;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Controller\EtablissementController;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use UnicaenApp\Service\EntityManagerAwareTrait;

class AdmissionController extends AdmissionAbstractController {

    use StructureServiceAwareTrait;
    use EntityManagerAwareTrait;
    use DisciplineServiceAwareTrait;
    use NotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;
    use AdmissionFormAwareTrait;
    use EtudiantServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use DocumentServiceAwareTrait;
    use VerificationServiceAwareTrait;
    use TypeValidationServiceAwareTrait;
    use AdmissionOperationRuleAwareTrait;
    use UserContextServiceAwareTrait;

    public function indexAction(): ViewModel|Response
    {
        $request = $this->getRequest();
        $id = $request->getPost('individuId');

        if (!empty($id)) {
            $this->multipageForm($this->admissionForm)->clearSession();
            return $this->redirect()->toRoute(
                'admission/ajouter',
                ['action' => "etudiant",
                    'individu' => $id],
                [],
                true);
        }

        $individu = $this->userContextService->getIdentityIndividu();

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
        $operations = [];

        if(!empty($admission)){
            $this->admissionOperationRule->injectOperationPossible($admission);
            $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
        }

        $admissions = $this->admissionService->getRepository()->findAll();
        return new ViewModel(['admissions' => $admissions, 'operations' => $operations, 'individu' => $individu]);
    }

    public function ajouterAction(): Response
    {
        return $this->multipageForm($this->admissionForm)
            ->setUsePostRedirectGet()
            ->setReuseRequestQueryParams() // la redir vers la 1ere étape conservera les nom, prenom issus de la recherche
            ->start(); // réinit du plugin et redirection vers la 1ère étape
    }

    /**
     * @throws NotSupported
     */
    public function etudiantAction(): Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }
        $data = $this->multipageForm($this->admissionForm)->getFormSessionData();

        //Récupération de l'objet Admission en BDD
        $admission = $this->getAdmission();
        if(!empty($admission)) {
            $this->admissionForm->bind($admission);
            if($data['_fieldset'] == "inscription"){
                //Enregistrement des informations de l'inscription
                $this->enregistrerInscription($data, $admission);
            }
        }

        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);
        $response->setVariable('admission', $admission);
        $response->setVariable('individu', $individu);
        $response->setTemplate('admission/ajouter-etudiant');

        return $response;
    }

    public function inscriptionAction(): Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }
        $data = $this->multipageForm($this->admissionForm)->getFormSessionData();

        //Initialise les valeurs de certains champs du fieldset (Etablissement, Directeur de thèse...)
        $this->initializeInscriptionFieldset();

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if($data['_fieldset'] == "etudiant") {
            //Enregistrement des informations de l'Etudiant
            $this->enregistrerEtudiant($data, $admission);

        } else {
            $this->admissionForm->bind($admission);
            //Enregistrement des informations de financement
            $this->enregistrerFinancement($data, $admission);
        }

        $response->setVariable('admission', $admission);
        $response->setTemplate('admission/ajouter-inscription');
        return $response;
    }

    public function financementAction() : Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }
        $data = $this->multipageForm($this->admissionForm)->getFormSessionData();

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if(!empty($admission)) {
            $this->admissionForm->bind($admission);
            if($data['_fieldset'] == "inscription"){
                //Enregistrement des informations de l'inscription
                $this->enregistrerInscription($data, $admission);
            }else{
                //Enregistrement des informations de financement
                $this->enregistrerDocument($data, $admission);
            }
        }

        $response->setVariable('admission', $admission);
        $response->setTemplate('admission/ajouter-financement');
        return $response;
    }

    /**
     * @throws NotSupported
     */
    public function documentAction(): Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }
        $data = $this->multipageForm($this->admissionForm)->getFormSessionData();

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        $documentsAdmission = [];
        $operations = [];
        if(!empty($admission)){
            $this->admissionForm->bind($admission);
            //Enregistrement des informations de Financement
            $this->enregistrerFinancement($data, $admission);

            $this->admissionOperationRule->injectOperationPossible($admission);
            $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);

            //Récupération des documents liés à ce dossier d'admission
            $documents = $this->documentService->getRepository()->findDocumentsByAdmission($admission);

            /** @var Document $document */
            foreach($documents as $document){
                if($document->getFichier() !== null){
                    $documentsAdmission[$document->getFichier()->getNature()->getCode()] = ['libelle'=>$document->getFichier()->getNomOriginal(), 'televersement' => $document->getFichier()->getHistoModification()->format('d/m/Y H:i')];
                }
            }
        }

        $response->setVariable('dataForm', $data);
        $response->setVariable('admission', $admission);
        $response->setVariable('operations', $operations);
        $response->setVariable('documents', $documentsAdmission);
        $response->setTemplate('admission/ajouter-document');
        return $response;
    }

    public function enregistrerAction()
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $data = $this->multipageForm($this->admissionForm)->getFormSessionData();

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if(!empty($admission)){
            $this->admissionForm->bind($admission);
            if($admission->getEtat()->getCode() == Etat::CODE_EN_COURS){
                //Enregistrement des informations de Document
                $this->enregistrerDocument($data, $admission);
            }
        }

        $this->multipageForm($this->admissionForm)->clearSession();
        return $this->redirect()->toRoute('admission');
    }

    public function supprimerAction(){
        $admission = $this->getAdmission();
        $individu = $admission->getIndividu();
        try{
            $this->admissionService->delete($admission);
        }catch (RuntimeException $e) {
            throw new RuntimeException("Un problème est survenu lors de la suppression du dossier d'admission",$e);
        }

        $this->multipageForm($this->admissionForm)->clearSession();
        $this->flashMessenger()->addSuccessMessage("Le dossier d'admission de {$individu} a bien été supprimé");
        return $this->redirect()->toRoute('admission');
    }

    public function notifierCommentairesAjoutesAction(): Response
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        $individu = $admission->getIndividu();
        try {
            $notif = $this->notificationFactory->createNotificationCommentairesAjoutes($admission);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::COMMENTAIRES_AJOUTES."]",0,$e);
        }

        $this->flashMessenger()->addSuccessMessage("{$individu} a bien été informé des commentaires ajoutés à son dossier d'admission");
        $redirectUrl = $this->params()->fromQuery('redirect');
        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }
    }

    //Envoi d'un mail à l'initiative de l'étudiant, afin de notifier le(s) gestionnaire(s) que le dossier est prêt à être vérifié
    public function notifierGestionnaireAction(): Response
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        try {
            $notif = $this->notificationFactory->createNotificationGestionnaire($admission);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::NOTIFICATION_GESTIONNAIRE."]",0,$e);
        }

        $this->flashMessenger()->addSuccessMessage("Vos gestionnaires ont bien été notifié de la fin de saisie de votre dossier d'admission");
        $redirectUrl = $this->params()->fromQuery('redirect');
        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }
    }

    public function rechercherIndividuAction(?string $type = null) : JsonModel
    {
        $type = $this->params()->fromQuery('type');
        if (($term = $this->params()->fromQuery('term'))) {
            $rows = $this->individuService->getRepository()->findByText($term, $type);
            $result = [];
            foreach ($rows as $row) {
                $prenoms = implode(' ', array_filter([$row['prenom1'], $row['prenom2'], $row['prenom3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $row['nom_usuel'] . ' ' . $prenoms;
                $extra = array(
                    'prenoms' => $prenoms,
                    'nom' => $row['nom_usuel'],
                    'email' => $row['email']
                );
                $result[] = array(
                    'id' => $row['id'], // identifiant unique de l'item
                    'label' => $label,     // libellé de l'item
                    'extras' => $extra,     // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }

    /**
     * @throws NotSupported
     */
    public function getAdmission(): Admission|null
    {
        $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
        return $this->admissionService->getRepository()->findOneByIndividu($individu);
    }

    public function enregistrerEtudiant($data, $admission)
    {
        //Si l'etudiant ne possède pas de dossier d'admission, on lui crée puis associe un fieldset etudiant
        if ($admission === null) {
            try {
                $individu = $this->individuService->getRepository()->findRequestedIndividu($this);

                /** @var Admission $admission */
                $admission = $this->admissionForm->getObject();
                $admission->setIndividu($individu);

                /** @var Etat $enCours */
                $enCours = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS]);
                $admission->setEtat($enCours);

                //Lier les valeurs des données en session avec le formulaire
                $this->admissionForm->get('etudiant')->bindValues($data['etudiant']);
                /** @var Etudiant $etudiant */
                $etudiant = $this->admissionForm->get('etudiant')->getObject();
                $etudiant->setAdmission($admission);
                $this->etudiantService->create($etudiant, $admission);

                $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage("Les informations concernant l'étape précédente n'ont pas pu être enregistrées.");
            }
        } else {
            //si le dossier d'admission existe, on met à jour l'entité Etudiant
            try {
                $this->admissionForm->bind($admission);
                if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
                    $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
                    //Lier les valeurs des données en session avec le formulaire
                    $this->admissionForm->get('etudiant')->bindValues($data['etudiant']);

                    $etudiant = $this->admissionForm->get('etudiant')->getObject();
                    $this->etudiantService->update($etudiant);
                }
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
            }
        }
        //Ajout de l'objet Vérification
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER) &&
            ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION))) {
            /** @var Verification $verification */
            $verification = $this->verificationService->getRepository()->findOneByEtudiant($etudiant);
            if ($verification === null) {
                /** @var Verification $verification */
                $verification = $this->admissionForm->get('etudiant')->get('verificationEtudiant')->getObject();
                $verification->setEtudiant($etudiant);
                $this->verificationService->create($verification);
            } else {
                /** @var Verification $updatedVerification */
                $updatedVerification = $this->admissionForm->get('etudiant')->get('verificationEtudiant')->getObject();
                $this->verificationService->update($updatedVerification);
            }
        }
    }

    public function enregistrerInscription($data, $admission)
    {
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            /** @var Inscription $inscription */
            $inscription = $this->inscriptionService->getRepository()->findOneByAdmission($admission);

            //Lier les valeurs des données en session avec le formulaire
            $this->admissionForm->get('inscription')->bindValues($data['inscription']);

            //Si le fieldest Inscription n'est pas encore en BDD
            if (!$inscription instanceof Inscription) {
                try {
                    /** @var Inscription $inscription */
                    $inscription = $this->admissionForm->get('inscription')->getObject();
                    //Ajout de la relation Inscription>Admission
                    $inscription->setAdmission($admission);
                    $this->inscriptionService->create($inscription);

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                }
            } else {
                try {
                    //Mise à jour de l'entité
                    /** @var Inscription $inscription */
                    $inscription = $this->admissionForm->get('inscription')->getObject();

                    $this->inscriptionService->update($inscription);
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
            }
            if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER)) {
                //Ajout de l'objet Vérification
                /** @var Verification $verification */
                $verification = $this->verificationService->getRepository()->findOneByInscription($inscription);
                if ($verification === null) {
                    /** @var Verification $verification */
                    $verification = $this->admissionForm->get('inscription')->get('verificationInscription')->getObject();
                    $verification->setInscription($inscription);
                    $this->verificationService->create($verification);
                } else {
                    /** @var Verification $updatedVerification */
                    $updatedVerification = $this->admissionForm->get('inscription')->get('verificationInscription')->getObject();
                    $this->verificationService->update($updatedVerification);
                }
            }
        }
    }

    public function enregistrerFinancement($data, $admission)
    {
        if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            /** @var Financement $financement */
            $financement = $this->financementService->getRepository()->findOneByAdmission($admission);

            //Lier les valeurs des données en session avec le formulaire
            $this->admissionForm->get('financement')->bindValues($data['financement']);

            //Si le fieldest Financement n'est pas encore en BDD
            if (!$financement instanceof Financement) {
                try {
                    /** @var Financement $financement */
                    $financement = $this->admissionForm->get('financement')->getObject();
                    //Ajout de la relation Financement>Admission
                    $financement->setAdmission($admission);
                    $this->financementService->create($financement);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                }
            } else {
                try {
                    /** @var Financement $financement */
                    $financement = $this->admissionForm->get('financement')->getObject();
                    $this->financementService->update($financement);
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
            }
            if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER)) {
                //Ajout de l'objet Vérification
                /** @var Verification $verification */
                $verification = $this->verificationService->getRepository()->findOneByFinancement($financement);
                if ($verification === null) {
                    /** @var Verification $verification */
                    $verification = $this->admissionForm->get('financement')->get('verificationFinancement')->getObject();
                    $verification->setFinancement($financement);
                    $this->verificationService->create($verification);
                } else {
                    /** @var Verification $updatedVerification */
                    $updatedVerification = $this->admissionForm->get('financement')->get('verificationFinancement')->getObject();
                    $this->verificationService->update($updatedVerification);
                }
            }
        }
    }

    public function enregistrerDocument($data, $admission)
    {
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            /** @var Document $document */
            $document = $this->documentService->getRepository()->findOneWhereNoFichierByAdmission($admission)[0] ?? null;

            //on en crée un Fieldset Document sans fichier
            //afin de relier une Vérification à celui-ci
            if (!$document instanceof Document) {
                try {
                    $document = new Document();
                    $document->setAdmission($admission);
                    $this->documentService->create($document);
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de l'envoi de votre dossier d'admission à vos gestionnaires.");
                }
            }

            //Ajout de l'objet Vérification
            if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER)) {
                $this->admissionForm->get('document')->bindValues($data['document']);
                /** @var Verification $verification */
                $verification = $this->verificationService->getRepository()->findOneByDocument($document);
                if ($verification === null) {
                    try {
                        /** @var Verification $verification */
                        $verification = $this->admissionForm->get('document')->get('verificationDocument')->getObject();
                        $verification->setDocument($document);
                        $this->verificationService->create($verification);
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                    }
                } else {
                    try {
                        $this->getAdmissionForm()->get('document')->get('verificationDocument')->setObject($verification);
                        $this->getAdmissionForm()->get('document')->get('verificationDocument')->populateValues($data['document']["verificationDocument"]);
                        /** @var Verification $updatedVerification */
                        $updatedVerification = $this->admissionForm->get('document')->get('verificationDocument')->getObject();
                        $this->verificationService->update($updatedVerification);
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                    }
                }
            }
        }
    }

    private function isUserDifferentFromUserInSession(){
        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);

        //Il faut que l'utilisateur existe, sinon on affiche une exception
        if ($individu === null) {
            $this->flashMessenger()->addErrorMessage("Individu spécifié introuvable");
            return $this->redirect()->toRoute('admission');
        }

        $data = $this->multipageForm($this->admissionForm)->getFormSessionData();
        //si l'individu est différent de celui en session, on vide les données en session et on redirige vers la première page du form
        if(array_key_exists('individu', $data["etudiant"])){
            $individuInSession = $data["etudiant"]['individu'];
            if((int)$individuInSession !== $individu->getId()){
                $this->multipageForm($this->admissionForm)->clearSession();
            }
        }
        return false;
    }
    private function initializeInscriptionFieldset(){
        //Partie Informations sur l'inscription
        /** @see AdmissionController::rechercherIndividuAction() */
        $this->admissionForm->get('inscription')->setUrlIndividuThese($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true));
        /** @see EtablissementController::rechercherAction() */
        $this->admissionForm->get('inscription')->setUrlEtablissement($this->url()->fromRoute('etablissement/rechercher', [], ["query" => []], true));

        $disciplines = $this->disciplineService->getDisciplinesAsOptions('code','ASC','code');
        $this->admissionForm->get('inscription')->setSpecialites($disciplines);

        $ecoles = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', false);
        $this->admissionForm->get('inscription')->setEcolesDoctorales($ecoles);

        $unites = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', false);
        $this->admissionForm->get('inscription')->setUnitesRecherche($unites);

        //Partie Spécifités envisagées
        /** @see PaysController::rechercherPaysAction() */
        $this->admissionForm->get('inscription')->setUrlPaysCoTutelle($this->url()->fromRoute('pays/rechercher-pays', [], ["query" => []], true));
    }
}