<?php
namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
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
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Etudiant\EtudiantServiceAwareTrait;
use Admission\Service\Exporter\Recapitulatif\RecapitulatifExporterAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Admission\Service\Operation\AdmissionOperationServiceAwareTrait;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Constants;
use Application\Controller\PaysController;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait as ApplicationFinancementServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Controller\EtablissementController;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Form\Element\SearchAndSelect;
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
    use FinancementServiceAwareTrait, ApplicationFinancementServiceAwareTrait  {
        FinancementServiceAwareTrait::getFinancementService insteadof ApplicationFinancementServiceAwareTrait;
        FinancementServiceAwareTrait::setFinancementService insteadof ApplicationFinancementServiceAwareTrait;
        ApplicationFinancementServiceAwareTrait::getFinancementService as getApplicationFinancementService;
        ApplicationFinancementServiceAwareTrait::setFinancementService as setApplicationFinancementService;
    }
    use DocumentServiceAwareTrait;
    use VerificationServiceAwareTrait;
    use AdmissionOperationRuleAwareTrait;
    use UserContextServiceAwareTrait;
    use RecapitulatifExporterAwareTrait;
    use EtablissementServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use AdmissionOperationServiceAwareTrait;
    use ConventionFormationDoctoraleServiceAwareTrait;

    public function indexAction(): ViewModel|Response
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $individu = $request->getPost('individuId');
            //Redirige vers le dossier d'admission de l'individu, si un individu est renseigné
            if ($individu && $individu['id']) {
                $this->multipageForm($this->admissionForm)->clearSession();
                return $this->redirect()->toRoute(
                    'admission/ajouter',
                    ['action' => "etudiant",
                        'individu' => $individu['id']],
                    [],
                    true);
            }
        }

        $inputIndividu = new SearchAndSelect();
        $inputIndividu
            ->setAutocompleteSource($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true))
            ->setAttributes([
                'class' => 'selectpicker show-tick',
                'data-live-search' => 'true',
                'id' => 'individuId',
                'name' => 'individuId',
            ]);

        $individu = $this->userContextService->getIdentityIndividu();
        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
        $operations = $admission ? $this->admissionOperationRule->getOperationsForAdmission($admission) : [];
        $operationEnAttente = $admission ? $this->getOperationEnAttente($admission) : null;
        $dossierComplet = $admission?->isDossierComplet();

        //Alimente le tableau de tous les dossiers d'admissions disponibles
        $admissions = $this->admissionService->getRepository()->findAll();
        unset($operations[TypeValidation::CODE_ATTESTATION_HONNEUR_CHARTE_DOCTORALE]);
        return new ViewModel([
            'admissions' => $admissions,
            'operations' => $operations,
            'individu' => $individu,
            'admission' => $admission,
            'inputIndividu' => $inputIndividu,
            'operationEnAttente' => $operationEnAttente,
            'dossierComplet' => $dossierComplet
        ]);
    }

    public function etudiantAction(): Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }

        //Récupération de l'objet Admission en BDD
        $admission = $this->getAdmission();
        if(!empty($admission)) {
            $data = $this->multipageForm($this->admissionForm)->getFormSessionData();
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
        } else if($data['_fieldset'] == "financement"){
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

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if(!empty($admission)) {
            $data = $this->multipageForm($this->admissionForm)->getFormSessionData();
            $this->admissionForm->bind($admission);
            if($data['_fieldset'] == "inscription"){
                //Enregistrement des informations de l'inscription
                $this->enregistrerInscription($data, $admission);
            }else if($data['_fieldset'] == "document"){
                //Enregistrement des informations de financement
                $this->enregistrerDocument($data, $admission);
            }
        }

        $origines = $this->getApplicationFinancementService()->findOriginesFinancements("libelleLong");
        $this->admissionForm->get('financement')->setFinancements($origines);

        $response->setVariable('admission', $admission);
        $response->setTemplate('admission/ajouter-financement');
        return $response;
    }

    public function documentAction(): Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }

        //Récupération de l'objet Admission en BDD
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        $documentsAdmission = [];
        $operations = [];
        $dossierValideParGestionnaire = null;
        $conventionFormationDoctorale = null;
        if(!empty($admission)){
            $data = $this->multipageForm($this->admissionForm)->getFormSessionData();
            $this->admissionForm->bind($admission);
            //Enregistrement des informations de Financement
            $this->enregistrerFinancement($data, $admission);

            $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
            $operationEnAttente = $this->getOperationEnAttente($admission);
            $dossierValideParGestionnaire = isset($operations[TypeValidation::CODE_VALIDATION_GESTIONNAIRE]) && $operations[TypeValidation::CODE_VALIDATION_GESTIONNAIRE]->getId() ? true : $dossierValideParGestionnaire;

            //Récupération des documents liés à ce dossier d'admission
            $documents = $this->documentService->getRepository()->findDocumentsByAdmission($admission);
            /** @var Document $document */
            foreach($documents as $document){
                if($document->getFichier() !== null){
                    $documentsAdmission[$document->getFichier()->getNature()->getCode()] = ['libelle'=>$document->getFichier()->getNomOriginal(), 'televersement' => $document->getFichier()->getHistoModification()->format('d/m/Y H:i')];
                }
            }
            $conventionFormationDoctorale = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $admission]);
        }

        $response->setVariable('admission', $admission);
        $response->setVariable('operations', $operations);
        $response->setVariable('documents', $documentsAdmission);
        $response->setVariable('dossierValideParGestionnaire', $dossierValideParGestionnaire);
        $response->setVariable('operationEnAttente', $operationEnAttente);
        $response->setVariable('conventionFormationDoctorale', $conventionFormationDoctorale);
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
            //Enregistrement des informations de Document
            $this->enregistrerDocument($data, $admission);
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
                $enCours = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS_SAISIE]);
                $admission->setEtat($enCours);

                //Lier les valeurs des données en session avec le formulaire
                $this->admissionForm->get('etudiant')->bindValues($data['etudiant']);
                /** @var Etudiant $etudiant */
                $etudiant = $this->admissionForm->get('etudiant')->getObject();
                $etudiant->setAdmission($admission);
                $this->etudiantService->create($etudiant, $admission);

                //Création également d'un fieldset Document sans Fichier
                //afin de relier une Vérification à celui-ci -> fait maintenant pour ensuite ajouter la charte sinon conflit
                $this->documentService->createDocumentWithoutFichier($admission);

                //On relie une charte du doctorat au dossier d'admission
                $this->documentService->addCharteDoctoraleToAdmission($admission);

                $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage("Les informations concernant l'étape précédente n'ont pas pu être enregistrées.");
            }
        } else {
            //si le dossier d'admission existe, on met à jour l'entité Etudiant
            try {
                $this->admissionForm->bind($admission);
                //Lier les valeurs des données en session avec le formulaire
                $this->admissionForm->get('etudiant')->bindValues($data['etudiant']);
                $etudiant = $this->admissionForm->get('etudiant')->getObject();
                if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
                    $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
                    $this->etudiantService->update($etudiant);
                }
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
            }
        }
        //Ajout de l'objet Vérification
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER) ) {
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
        /** @var Inscription $inscription */
        $inscription = $this->inscriptionService->getRepository()->findOneByAdmission($admission);
        //Lier les valeurs des données en session avec le formulaire
        $this->admissionForm->get('inscription')->bindValues($data['inscription']);
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
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

    public function enregistrerFinancement($data, $admission)
    {
        /** @var Financement $financement */
        $financement = $this->admissionFinancementService->getRepository()->findOneByAdmission($admission);
        //Lier les valeurs des données en session avec le formulaire
        $this->admissionForm->get('financement')->bindValues($data['financement']);
        if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            //Si le fieldest Financement n'est pas encore en BDD
            if (!$financement instanceof Financement) {
                try {
                    /** @var Financement $financement */
                    $financement = $this->admissionForm->get('financement')->getObject();
                    //Ajout de la relation Financement>Admission
                    $financement->setAdmission($admission);
                    $this->admissionFinancementService->create($financement);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                }
            } else {
                try {
                    /** @var Financement $financement */
                    $financement = $this->admissionForm->get('financement')->getObject();
                    $this->admissionFinancementService->update($financement);
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
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

    public function enregistrerDocument($data, $admission)
    {
//        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
//            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            /** @var Document $document */
            $document = $this->documentService->getRepository()->findOneWhereNoFichierByAdmission($admission)[0] ?? null;

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
                        $this->admissionForm->get('document')->get('verificationDocument')->setObject($verification);
                        $this->admissionForm->get('document')->get('verificationDocument')->populateValues($data['document']["verificationDocument"]);
                        /** @var Verification $updatedVerification */
                        $updatedVerification = $this->admissionForm->get('document')->get('verificationDocument')->getObject();
                        $this->verificationService->update($updatedVerification);
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                    }
                }
            }
//        }
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

        $etablissementsInscription = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions();
        $this->admissionForm->get('inscription')->setEtablissementsInscription($etablissementsInscription);

        //Partie Spécifités envisagées
        /** @see PaysController::rechercherPaysAction() */
        $this->admissionForm->get('inscription')->setUrlPaysCoTutelle($this->url()->fromRoute('pays/rechercher-pays', [], ["query" => []], true));
    }

    public function genererStatutDossierAction(): ViewModel|Response
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        $operations = $admission ? $this->admissionOperationRule->getOperationsForAdmission($admission) : [];
        $operationEnAttente = $admission ? $this->getOperationEnAttente($admission) : null;
        $dossierComplet = $admission?->isDossierComplet();

        return new ViewModel([
            'operations' => $operations,
            'admission' => $admission,
            'operationEnAttente' => $operationEnAttente,
            'dossierComplet' => $dossierComplet,
            'showActionButtons' => false
        ]);
    }
    public function getOperationEnAttente(Admission $admission){
        $operationLastCompleted = $this->admissionOperationRule->findLastCompletedOperation($admission);
        $operationEnAttente = $operationLastCompleted ? $this->admissionOperationRule->findFollowingOperation($operationLastCompleted) : null;

        if($operationEnAttente instanceof AdmissionValidation){
            $codeTypeValidation = $operationEnAttente->getTypeValidation()->getCode();
            $operationEnAttente = ($codeTypeValidation !== TypeValidation::CODE_ATTESTATION_HONNEUR && $codeTypeValidation !== TypeValidation::CODE_ATTESTATION_HONNEUR_CHARTE_DOCTORALE) ? $operationEnAttente : null;
            $operationEnAttente = $operationLastCompleted && $operationLastCompleted->getValeurBool() ? $operationEnAttente : false;
        }elseif($operationEnAttente instanceof AdmissionAvis){
            $operationEnAttente = $operationLastCompleted && $operationLastCompleted->getValeurBool() ? $operationEnAttente : false;
        }
        return $operationEnAttente;
    }

    /** TEMPLATES RENDERER *******************************************************************************/

    public function notifierCommentairesAjoutesAction()
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

//    public function notifierDossierCompletAction()
//    {
//        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
//        $individu = $admission->getIndividu();
//        try {
//            $notif = $this->notificationFactory->createNotificationDossierComplet($admission);
//            $this->notifierService->trigger($notif);
//        } catch (RuntimeException $e) {
//            throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::NOTIFICATION_DOSSIER_COMPLET."]",0,$e);
//        }
//
//        $this->flashMessenger()->addSuccessMessage("{$individu} a bien été informé que son dossier d'admission est complet");
//        $redirectUrl = $this->params()->fromQuery('redirect');
//        if ($redirectUrl !== null) {
//            return $this->redirect()->toUrl($redirectUrl);
//        }
//    }

    public function notifierDossierIncompletAction()
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        $individu = $admission->getIndividu();
        try {
            $notif = $this->notificationFactory->createNotificationDossierIncomplet($admission);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::NOTIFICATION_DOSSIER_COMPLET."]",0,$e);
        }

        $this->flashMessenger()->addSuccessMessage("{$individu} a bien été informé que son dossier d'admission est incomplet");

        /** @var AdmissionValidation $operationLastCompleted */
        $operationLastCompleted = $this->admissionOperationRule->findLastCompletedOperation($admission);
        if($operationLastCompleted instanceof AdmissionValidation && $operationLastCompleted->getTypeValidation()->getCode() === TypeValidation::CODE_ATTESTATION_HONNEUR){
            $messages = [
                'success' => sprintf(
                    "L'opération suivante a été annulée car le dossier d'admission a été déclaré incomplet le %s par %s : %s.",
                    ($operationLastCompleted->getHistoModification() ?: $operationLastCompleted->getHistoCreation())->format(Constants::DATETIME_FORMAT),
                    $operationLastCompleted->getHistoModificateur() ?: $operationLastCompleted->getHistoCreateur(),
                    lcfirst($operationLastCompleted),
                ),
            ];
            $this->admissionOperationService->deleteOperationAndThrowEvent($operationLastCompleted, $messages);

            /** @var Etat $enCours */
            $enCours = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS_SAISIE]);
            $admission->setEtat($enCours);
            $this->admissionService->update($admission);
        }

        $redirectUrl = $this->params()->fromQuery('redirect');
        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }
    }

    //Envoi d'un mail à l'initiative de l'étudiant, afin de notifier le(s) gestionnaire(s) que le dossier est prêt à être vérifié
    public function notifierGestionnaireAction()
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        try {
            $notif = $this->notificationFactory->createNotificationGestionnaire($admission);
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::NOTIFICATION_GESTIONNAIRE."]",0,$e);
        }

        $this->flashMessenger()->addSuccessMessage("Votre gestionnaire a bien été notifié de la fin de saisie de votre dossier d'admission");
        $redirectUrl = $this->params()->fromQuery('redirect');
        if ($redirectUrl !== null) {
            return $this->redirect()->toUrl($redirectUrl);
        }
    }
    public function genererRecapitulatifAction()
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        $logos = [];
        try {
            $site = $admission->getInscription()->first()->getComposanteDoctorat() ? $admission->getInscription()->first()->getComposanteDoctorat()->getStructure() : null;
            $logos['site'] = $site ? $this->fichierStorageService->getFileForLogoStructure($site) : null;
        } catch (StorageAdapterException $e) {
            $logos['site'] = null;
        }
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $logos['comue'] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException $e) {
                $logos['comue'] = null;
            }
        }

        $operations = $admission ? $this->admissionOperationRule->getOperationsForAdmission($admission) : null;
        $export = $this->recapitulatifExporter;
        $export->setWatermark("CONFIDENTIEL");
        $export->setVars([
            'admission' => $admission,
            'logos' => $logos,
            'operations' => $operations
        ]);
        $export->export('SYGAL_admission_recapitulatif_' . $admission->getId() . ".pdf");
    }
}