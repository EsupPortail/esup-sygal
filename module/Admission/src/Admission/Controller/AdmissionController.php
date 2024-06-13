<?php
namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Document;
use Admission\Entity\Db\Etat;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\TypeValidation;
use Admission\Entity\Db\Verification;
use Admission\Form\Admission\AdmissionFormAwareTrait;
use Admission\Form\Fieldset\Document\DocumentFieldset;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Provider\Template\MailTemplates;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Service\Admission\AdmissionRechercheServiceAwareTrait;
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
use Application\Controller\PaysController;
use Application\Entity\Db\Role;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait as ApplicationFinancementServiceAwareTrait;
use Application\Service\Pays\PaysServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Mpdf\MpdfException;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Form\Fieldset\MultipageFormNavFieldset;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\View\Model\CsvModel;

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
    use RoleServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use AdmissionRechercheServiceAwareTrait;
    use PaysServiceAwareTrait;

    public function indexAction(): ViewModel|Response
    {
        return $this->redirect()->toRoute('admission/recherche', [], [], true);
    }

    public function etudiantAction(): Response|ViewModel
    {
        //Vide la session, si l'utilisateur demandé est différent de celui en session
        $this->isUserDifferentFromUserInSession();

        $response = $this->processMultipageForm($this->admissionForm);
        if ($response instanceof Response) {
            return $response;
        }

        $etudiant = $this->admissionForm->get('etudiant');
        if($etudiant instanceof EtudiantFieldset){
            $pays = $this->paysService->getPaysAsOptions();
            $etudiant->setPays($pays);

            $nationalites = $this->paysService->getNationalitesAsOptions();
            $etudiant->setNationalites($nationalites);
        }

        //Récupération de l'objet Admission en BDD
        $admission = $this->getAdmission();
        if(!empty($admission)) {
            $data = $this->multipageForm($this->admissionForm)->getFormSessionData();
            $this->admissionForm->bind($admission);
            $canModifierAdmission  = $this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION) || $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION);
            if(!$canModifierAdmission){
                $etudiant->disableModificationFieldset();
            }
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

        if(!empty($admission)){
            $canModifierAdmission  = $this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION) || $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION);
            if(!$canModifierAdmission){
                /** @var InscriptionFieldset $inscription */
                $inscription = $this->admissionForm->get('inscription');
                $inscription->disableModificationFieldset();
            }
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
            $canModifierAdmission  = $this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION) || $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION);
            if(!$canModifierAdmission){
                /** @var FinancementFieldset $financement */
                $financement = $this->admissionForm->get('financement');
                $financement->disableModificationFieldset();
            }
            if($data['_fieldset'] == "inscription"){
                //Enregistrement des informations de l'inscription
                $this->enregistrerInscription($data, $admission);
            }else if($data['_fieldset'] == "document"){
                //Enregistrement des informations de financement
                $this->enregistrerDocument($data, $admission);
            }
        }

        $origines = $this->getApplicationFinancementService()->findOriginesFinancements("libelleLong");
        /** @var FinancementFieldset $financement */
        $financement = $this->admissionForm->get('financement');
        $financement->setFinancements($origines);

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
        if(!empty($admission)){
            $data = $this->multipageForm($this->admissionForm)->getFormSessionData();
            $this->admissionForm->bind($admission);
            $this->admissionForm->setNavigationFieldsetPrototype($this->createNavigationForDocument($admission));

            $canModifierAdmission  = $this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION) || $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION);
            if(!$canModifierAdmission){
                /** @var DocumentFieldset $document */
                $document = $this->admissionForm->get('document');
                $document->disableModificationFieldset();
            }
            //Enregistrement des informations de Financement
            $this->enregistrerFinancement($data, $admission);

            //Récupération des opérations liées au dossier d'admission
            $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
            //Masquage des actions non voulues dans le circuit de signatures -> celles correspondant à la convention de formation doctorale
            $operations = $this->admissionOperationService->hideOperations($operations, TypeValidation::CODE_VALIDATIONS_CONVENTION_FORMATION_DOCTORALE);
            //Récupération des opérations liées à la convention de formation doctorale du dossier d'admission
            $conventionFormationDoctoraleOperations = $this->admissionOperationRule->getOperationsForAdmission($admission, 'conventionFormationDoctorale');
            $operationEnAttente = $this->admissionOperationRule->getOperationEnAttente($admission);
            $role = $this->userContextService->getSelectedIdentityRole();
            $isOperationAllowedByRole = !$operationEnAttente || $this->admissionOperationRule->isOperationAllowedByRole($operationEnAttente, $role);
            //Récupération des documents liés à ce dossier d'admission
            $documents = $this->documentService->getRepository()->findDocumentsByAdmission($admission);
            /** @var Document $document */
            foreach($documents as $document){
                if($document->getFichier() !== null){
                    $documentsAdmission[$document->getFichier()->getNature()->getCode()] = [
                        'libelle'=>$document->getFichier()->getNomOriginal(),
                        'size' => $document->getFichier()->getTaille(),
                        'televersement' => $document->getFichier()->getHistoModification()->format('d/m/Y H:i')
                    ];
                }
            }
            $conventionFormationDoctorale = $this->conventionFormationDoctoraleService->getRepository()->findOneBy(["admission" => $admission]);
            $commentaires = $this->admissionService->getCommentaires($admission);
        }

        $response->setVariable('admission', $admission);
        $response->setVariable('operations', $operations ?? []);
        $response->setVariable('documents', $documentsAdmission ?? []);
        $response->setVariable('operationEnAttente', $operationEnAttente ?? null);
        $response->setVariable('conventionFormationDoctorale', $conventionFormationDoctorale ?? null);
        $response->setVariable('conventionFormationDoctoraleOperations', $conventionFormationDoctoraleOperations ?? null);
        $response->setVariable('isOperationAllowedByRole', $isOperationAllowedByRole ?? null);
        $response->setVariable('commentaires', $commentaires ?? null);
        $response->setTemplate('admission/ajouter-document');
        return $response;
    }

    public function enregistrerAction(): Response
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

        //Permet d'enregistrer les commentaires entrés par la/le gestionnaire du dossier
        if(isset($data["document"]["enregistrerVerification"]) && $data["document"]["enregistrerVerification"] === "enregistrerVerification"){
            $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
            return $this->redirect()->toRoute('admission/ajouter', ['action' => 'document', 'individu' => $individu->getId()]);
        }else{
            $this->multipageForm($this->admissionForm)->clearSession();
            return $this->redirect()->toRoute('admission');
        }
    }

    /**
     * @throws ORMException
     */
    public function supprimerAction(): Response
    {
        $admission = $this->getAdmission();
        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first() ? $admission->getInscription()->first() : null;
        $individu = $admission->getIndividu();
        try{
            $directeur = $inscription?->getDirecteur();
            $coDirecteur = $inscription?->getCoDirecteur();

            $this->admissionService->delete($admission);

            //Suppression des rôles pour les directeurs/co-directeurs/candidat de thèse de ce dossier d'admission
            $this->gererRoleIndividu($individu, Role::ROLE_ID_ADMISSION_CANDIDAT);
            $this->gererRoleIndividu($directeur, Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE);
            $this->gererRoleIndividu($coDirecteur, Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE);
        }catch (ORMException $e) {
            throw new ORMException("Un problème est survenu lors de la suppression du dossier d'admission",$e);
        }

        $this->multipageForm($this->admissionForm)->clearSession();
        $this->flashMessenger()->addSuccessMessage("Le dossier d'admission de $individu a bien été supprimé");
        return $this->redirect()->toRoute('admission');
    }

    public function rechercherIndividuAction() : JsonModel
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

    public function enregistrerEtudiant(array $data, Admission|null $admission): void
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

                //Ajout du rôle Candidat à la personne reliée au dossier d'admission
                $this->gererRoleIndividu($individu, Role::ROLE_ID_ADMISSION_CANDIDAT);

                $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
            } catch (Exception $e) {
                $this->flashMessenger()->addErrorMessage("Les informations concernant l'étape précédente n'ont pas pu être enregistrées : ".$e->getMessage());
            }
        } else {
            //si le dossier d'admission existe, on met à jour l'entité Etudiant
            try {
                $this->admissionForm->bind($admission);
                if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
                    $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
                    //Lier les valeurs des données en session avec le formulaire
                    $this->admissionForm->get('etudiant')->bindValues($data['etudiant']);
                    $this->etudiantService->update($this->admissionForm->get('etudiant')->getObject());
                }
            } catch (Exception $e) {
                $this->flashMessenger()->addErrorMessage("Échec de la modification des informations : ".$e->getMessage());
            }
        }
        //Ajout de l'objet Vérification
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER) ) {
            $etudiant = $this->admissionForm->get('etudiant')->getObject();
            if($etudiant instanceof Etudiant){
                /** @var Verification $verification */
                $verification = $this->verificationService->getRepository()->findOneByEtudiant($etudiant);
                /** @var EtudiantFieldset $etudiantFieldset */
                $etudiantFieldset = $this->admissionForm->get('etudiant');
                if(isset($data['etudiant']['verificationEtudiant'])){
                    $etudiantFieldset->get('verificationEtudiant')->bindValues($data['etudiant']['verificationEtudiant']);
                }

                /** @var Verification $verificationEtudiant */
                $verificationEtudiant = $etudiantFieldset->get('verificationEtudiant')->getObject();
                if ($verification === null) {
                    $verificationEtudiant->setEtudiant($etudiant);
                    $this->verificationService->create($verificationEtudiant);
                } else {
                    $this->verificationService->update($verificationEtudiant);
                }
            }
        }
    }

    public function enregistrerInscription(array $data, Admission $admission): void
    {
        /** @var Inscription $inscription */
        $inscription = $this->inscriptionService->getRepository()->findOneByAdmission($admission);
        $directeurBeforeUpdate = $inscription?->getDirecteur();
        $coDirecteurBeforeUpdate = $inscription?->getCoDirecteur();
        $etablissementInscriptionBeforeUpdate = $inscription?->getEtablissementInscription();

        //Lier les valeurs des données en session avec le formulaire
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            $this->admissionForm->get('inscription')->bindValues($data['inscription']);
            //Si le fieldest Inscription n'est pas encore en BDD
            if (!$inscription instanceof Inscription) {
                try {
                    /** @var Inscription $inscription */
                    $inscription = $this->admissionForm->get('inscription')->getObject();
                    //Ajout de la relation Inscription>Admission
                    $inscription->setAdmission($admission);
                    $this->inscriptionService->create($inscription);

                    //On relie une charte du doctorat au dossier d'admission
                    $this->documentService->addCharteDoctoraleToAdmission($inscription);

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations : ".$e->getMessage());
                }
            } else {
                try {
                    //Mise à jour de l'entité
                    /** @var Inscription $inscription */
                    $inscription = $this->admissionForm->get('inscription')->getObject();
                    $this->inscriptionService->update($inscription);

                    //Si on l'établissement d'inscription est modifié, on supprime l'ancienne charte doctorale, puis on ajoute la nouvelle
                    if($etablissementInscriptionBeforeUpdate !== $inscription->getEtablissementInscription()){
                        $charteDoctorat = $this->documentService->getRepository()->findByAdmissionAndNature($admission, NatureFichier::CODE_ADMISSION_CHARTE_DOCTORAT);
                        if($charteDoctorat){
                            $this->documentService->delete($charteDoctorat);
                        }
                        //On relie une charte du doctorat au dossier d'admission
                        $this->documentService->addCharteDoctoraleToAdmission($inscription);
                    }
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations : ".$e->getMessage());
                }
            }
            //Gestion d'ajout/suppression de rôle au directeur et co-directeur de thèse
            if($directeurBeforeUpdate !== $inscription->getDirecteur()){
                //Gestion du Potentiel directeur de thèse qui n'est plus relié en tant que directeur de thèse du dossier d'admission
                $this->gererRoleIndividu($directeurBeforeUpdate, Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE);
                //Ajout du rôle Potentiel directeur de thèse à la nouvelle personne reliée en tant que directeur de thèse du dossier d'admission
                $this->gererRoleIndividu($inscription->getDirecteur(), Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE);
            }

            if($coDirecteurBeforeUpdate !== $inscription->getCoDirecteur()){
                //Gestion du Potentiel co-directeur de thèse qui n'est plus relié en tant que co-directeur de thèse du dossier d'admission
                $this->gererRoleIndividu($coDirecteurBeforeUpdate, Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE);
                //Ajout du rôle Potentiel co-directeur de thèse à la nouvelle personne reliée en tant que co-directeur de thèse du dossier d'admission
                $this->gererRoleIndividu($inscription->getCoDirecteur(), Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE);
            }
        }

        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER)) {
            if($inscription instanceof Inscription){
                //Ajout de l'objet Vérification
                /** @var Verification $verification */
                $verification = $this->verificationService->getRepository()->findOneByInscription($inscription);
                /** @var InscriptionFieldset $inscriptionFieldset */
                $inscriptionFieldset = $this->admissionForm->get('inscription');
                if(isset($data['inscription']['verificationInscription'])){
                    $inscriptionFieldset->get('verificationInscription')->bindValues($data['inscription']['verificationInscription']);
                }

                /** @var Verification $verificationInscription */
                $verificationInscription = $inscriptionFieldset->get('verificationInscription')->getObject();
                if ($verification === null) {
                    $verificationInscription->setInscription($inscription);
                    $this->verificationService->create($verificationInscription);
                } else {
                    $this->verificationService->update($verificationInscription);
                }
            }
        }
    }

    public function enregistrerFinancement(array $data, Admission $admission): void
    {
        /** @var Financement $financement */
        $financement = $this->admissionFinancementService->getRepository()->findOneByAdmission($admission);
        //Lier les valeurs des données en session avec le formulaire
        if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
            $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION)) {
            $this->admissionForm->get('financement')->bindValues($data['financement']);
            //Si le fieldest Financement n'est pas encore en BDD
            if (!$financement instanceof Financement) {
                try {
                    /** @var Financement $financement */
                    $financement = $this->admissionForm->get('financement')->getObject();
                    //Ajout de la relation Financement>Admission
                    $financement->setAdmission($admission);
                    $this->admissionFinancementService->create($financement);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations : ".$e->getMessage());
                }
            } else {
                try {
                    /** @var Financement $financement */
                    $financement = $this->admissionForm->get('financement')->getObject();
                    $this->admissionFinancementService->update($financement);
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations : ".$e->getMessage());
                }
            }
        }
        if ($this->isAllowed($admission,AdmissionPrivileges::ADMISSION_VERIFIER)) {
            if($financement instanceof Financement){
                //Ajout de l'objet Vérification
                /** @var Verification $verification */
                $verification = $this->verificationService->getRepository()->findOneByFinancement($financement);
                /** @var FinancementFieldset $financementFieldset */
                $financementFieldset = $this->admissionForm->get('financement');
                if(isset($data['financement']['verificationFinancement'])){
                    $financementFieldset->bindValues($data['financement']);
                }

                /** @var Verification $verification */
                $verificationFinancement = $financementFieldset->get('verificationFinancement')->getObject();
                if ($verification === null) {
                    $verificationFinancement->setFinancement($financement);
                    $this->verificationService->create($verificationFinancement);
                } else {
                    $this->verificationService->update($verificationFinancement);
                }
            }
        }
    }

    public function enregistrerDocument(array $data, Admission $admission): void
    {
        /** @var Document $document */
        $document = $this->documentService->getRepository()->findOneWhereNoFichierByAdmission($admission)[0] ?? null;
        //Ajout de l'objet Vérification
        if ($this->isAllowed($admission, AdmissionPrivileges::ADMISSION_VERIFIER)) {
            $this->admissionForm->get('document')->bindValues($data['document']);
            /** @var Verification $verification */
            $verification = $this->verificationService->getRepository()->findOneByDocument($document);
            /** @var DocumentFieldset $documentFieldset */
            $documentFieldset = $this->admissionForm->get('document');
            /** @var Verification $verificationDocument */
            $verificationDocument = $documentFieldset->get('verificationDocument')->getObject();
            if ($verification === null) {
                $verificationDocument->setDocument($document);
                $this->verificationService->create($verificationDocument);
            } else {
                $this->verificationService->update($verificationDocument);
            }
        }
    }

    private function isUserDifferentFromUserInSession(): Response|bool
    {
        //si le paramètre refresh est présent dans l'url, on vide les données en session
        $refresh = $this->params()->fromQuery("refresh");
        if($refresh){
            $this->multipageForm($this->admissionForm)->clearSession();
        }

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

    private function initializeInscriptionFieldset(): void
    {
        $inscription = $this->admissionForm->get('inscription');
        if($inscription instanceof InscriptionFieldset){
            //Partie Informations sur l'inscription
            /** @see AdmissionController::rechercherIndividuAction() */
            $inscription->setUrlIndividuThese($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true));

            $disciplines = $this->disciplineService->getDisciplinesAsOptions('code','ASC','code');
            $inscription->setSpecialites($disciplines);

            $composantes = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_COMPOSANTE_ENSEIGNEMENT, 'structure.libelle', false);
            $inscription->setComposantesEnseignement($composantes);

            $ecoles = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);
            $inscription->setEcolesDoctorales($ecoles);

            $unites = $this->structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'structure.libelle', false);
            $inscription->setUnitesRecherche($unites);

            $etablissementsInscription = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions();
            $inscription->setEtablissementsInscription($etablissementsInscription);

            $qualites = $this->qualiteService->getQualitesForAdmission();
            $inscription->setQualites($qualites);

            //Partie Spécifités envisagées
            /** @see PaysController::rechercherPaysAction() */
            $inscription->setUrlPaysCoTutelle($this->url()->fromRoute('pays/rechercher-pays', [], ["query" => []], true));
        }

    }

    public function genererStatutDossierAction(): ViewModel|Response
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);

        //Récupération des opérations liées au dossier d'admission
        $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
        //Masquage des actions non voulues dans le circuit de signatures -> celles correspondant à la convention de formation doctorale
        $operations = $this->admissionOperationService->hideOperations($operations, TypeValidation::CODE_VALIDATIONS_CONVENTION_FORMATION_DOCTORALE);
        $operationEnAttente = $admission ? $this->admissionOperationRule->getOperationEnAttente($admission) : null;
        $role = $this->userContextService->getSelectedIdentityRole();
        $isOperationAllowedByRole = !$operationEnAttente || $this->admissionOperationRule->isOperationAllowedByRole($operationEnAttente, $role);
        $commentaires = $admission ? $this->admissionService->getCommentaires($admission) : null;

        return new ViewModel([
            'operations' => $operations,
            'admission' => $admission,
            'operationEnAttente' => $operationEnAttente,
            'showActionButtons' => false,
            'isOperationAllowedByRole' => $isOperationAllowedByRole,
            'commentaires' => $commentaires
        ]);
    }

    public function gererRoleIndividu(Individu|null $individu, string $roleId): void
    {
        if($individu){
            $role = $this->roleService->getRepository()->findOneBy(["roleId" => $roleId]);
            $hasRole = $this->roleService->findOneIndividuRole($individu, $role);
            //Si l'individu ne possède pas ce rôle, on lui ajoute
            if (!$hasRole) {
                $this->roleService->addRole($individu, $role->getId());
                //Si l'individu possède ce rôle, on regarde s'il possède encore des dossiers d'admissions
                //si c'est non, on lui enlève le rôle
            } else {
                if($role->getRoleId() == Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE){
                    $admissionsIndividu = $this->inscriptionService->getRepository()->findBy(["directeur" => $individu]);
                }else if($role->getRoleId() == Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE){
                    $admissionsIndividu = $this->inscriptionService->getRepository()->findBy(["coDirecteur" => $individu]);
                }
                if(empty($admissionsIndividu)){
                    $this->roleService->removeRole($individu, $role->getId());
                }
            }
        }
    }
    protected function createNavigationForDocument(Admission $admission): MultipageFormNavFieldset
    {
        $navigationElement = MultipageFormNavFieldset::create();
        $navigationElement->setCancelEnabled(false);
        $nextButton = $navigationElement->getNextButton();
        $prevButton = $navigationElement->getPreviousButton();
        $submitButton = $navigationElement->getSubmitButton();
        $confirmButton = $navigationElement->getConfirmButton();
        $cancelButton = $navigationElement->getCancelButton();

        $nextButton->setAttribute('class', $nextButton->getAttribute('class') . ' btn btn-primary');
        $prevButton->setAttribute('class', $prevButton->getAttribute('class') . ' btn btn-primary');
        $cancelButton->setAttribute('class', $confirmButton->getAttribute('class') . ' visually-hidden');

        $canModifierAdmission = $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_SON_DOSSIER_ADMISSION) ||
                                $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_MODIFIER_TOUS_DOSSIERS_ADMISSION);
        $canVerifierAdmission = $this->isAllowed($admission, AdmissionPrivileges::ADMISSION_VERIFIER);
        //si le dossier est validé, rejeté, en cours de validation ou abandonné et que l'utilisateur connecté n'a pas le droit de modifier le dossier
        if(!$canModifierAdmission){
            $submitButton->setValue("Revenir à l'accueil");
            $submitButton->setAttribute('class', $submitButton->getAttribute('class') . ' btn btn-primary');
            $submitButton->setAttribute('title', "Revenir à la page d'accueil du module");
            //si le dossier est en cours de saisie et que l'utilisateur connecté a le droit de modifier le dossier
        }else{
            $submitButton->setValue("Enregistrer");
            $submitButton->setAttribute('class', $submitButton->getAttribute('class') . ' btn btn-success');
            $submitButton->setAttribute('title', "Enregistrer les possibles modifications faites sur le dossier");
        }
        return $navigationElement;
    }

    public function genererExportCsvAction(): Response|CsvModel
    {
        $queryParams = $this->params()->fromQuery();

        $ecoleDoctoraleSourceCode = $this->params()->fromQuery("ecoleDoctorale");
        $role = $this->userContextService->getSelectedRoleEcoleDoctorale();
        $ecoleDoctorale = ($role && $role->getStructure()) ? $role->getStructure()->getEcoleDoctorale() : null;
        if(($ecoleDoctoraleSourceCode && $role && $ecoleDoctorale) && $ecoleDoctoraleSourceCode !== $ecoleDoctorale->getSourceCode()){
            $this->flashMessenger()->addErrorMessage("Vous n'avez pas les droits nécessaires pour générer ces dossiers d'admission");
            return $this->redirect()->toRoute('admission');
        }

        foreach ($queryParams as $key => $value) {
            if ($key === 'search' || empty($value)) {
                unset($queryParams[$key]);
            }
        }

        $this->admissionRechercheService->init();
        if($queryParams) $this->admissionRechercheService->processQueryParams($queryParams);
        $qb = $this->admissionRechercheService->getQueryBuilder();
        $qb
            ->andWhere($qb->expr()->orX('admission.etat = :etat'))
            ->setParameter('etat', Etat::CODE_VALIDE);
        $listing = $qb->getQuery()->getResult();

        //export
        $headers = ['numero_candidat', 'sexe', 'nom_famille', 'nom_usuel', 'prenom', 'prenom2', 'prenom3',	'date_naissance', 'code_commune_naissance',
            'libellé_commune_naissance', 'code_pays_naissance',	'code_nationalite',	'ine', 'adresse_code_pays',	'adresse_ligne1_etage',
            'adresse_ligne2_batiment',	'adresse_ligne3_voie',	'adresse_ligne4_complement', 'adresse_code_postal',	'adresse_code_commune',
            'adresse_cp_ville_etranger', 'numero_telephone1', 'numero_telephone2', 'courriel'
        ];
        $records = [];
        /** @var Admission $admission */
        foreach ($listing as $admission) {
            $entry = [];
            /** @var Etudiant $etudiant */
            $etudiant = $admission->getEtudiant()->first();
            $entry['numero_candidat'] = $etudiant->getNumeroCandidat();
            $entry['sexe'] = rtrim($etudiant->getSexe(), '.');
            $entry['nom_famille'] = $etudiant->getNomFamille();
            $entry['nom_usuel'] = $etudiant->getNomUsuel();
            $entry['prenom'] = $etudiant->getPrenom();
            $entry['prenom2'] = $etudiant->getPrenom2();
            $entry['prenom3'] = $etudiant->getPrenom3();
            $entry['date_naissance'] = $etudiant->getDateNaissance();
            $entry['code_commune_naissance'] = $etudiant->getCodeCommuneNaissance();
            $entry['libellé_commune_naissance'] = $etudiant->getLibelleCommuneNaissance();
            $entry['code_pays_naissance'] = $etudiant->getPaysNaissance() ? $etudiant->getPaysNaissance()->getCodePaysApogee() : null;
            $entry['code_nationalite'] = $etudiant->getNationalite() ? $etudiant->getNationalite()->getCodePaysApogee() : null;
            $entry['ine'] = $etudiant->getIne();
            $entry['adresse_code_pays'] = $etudiant->getAdresseCodePays() ? $etudiant->getAdresseCodePays()->getCodePaysApogee() : null;
            $entry['adresse_ligne1_etage'] = $etudiant->getAdresseLigne1Etage();
            $entry['adresse_ligne2_batiment'] = $etudiant->getAdresseLigne2Batiment();
            $entry['adresse_ligne3_voie'] = $etudiant->getAdresseLigne3voie();
            $entry['adresse_ligne4_complement'] = $etudiant->getAdresseLigne4Complement();
            $entry['adresse_code_postal'] = $etudiant->getAdresseCodePostal();
            $entry['adresse_code_commune'] = $etudiant->getAdresseCodeCommune();
            $entry['adresse_cp_ville_etranger'] = $etudiant->getAdresseCpVilleEtrangere();
            $entry['numero_telephone1'] = (string)$etudiant->getNumeroTelephone1();
            $entry['numero_telephone2'] = (string)$etudiant->getNumeroTelephone2();
            $entry['courriel'] = $etudiant->getCourriel();
            $records[] = $entry;
        }
        $filename = (new DateTime())->format('Ymd') . '_admissions.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }

    /** TEMPLATES RENDERER *******************************************************************************/
    //Mail à l'initiative du gestionnaire, afin de notifier l'étudiant que son dossier d'admission est incomplet
    public function notifierDossierIncompletAction()
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        $individu = $admission->getIndividu();

        /** @var AdmissionValidation $operationLastCompleted */
        $operationLastCompleted = $this->admissionOperationRule->findLastCompletedOperation($admission);
        if($operationLastCompleted instanceof AdmissionValidation && $operationLastCompleted->getTypeValidation()->getCode() === TypeValidation::CODE_ATTESTATION_HONNEUR){
            // historisation
            $this->admissionOperationService->deleteOperation($operationLastCompleted);

            try {
                $notif = $this->notificationFactory->createNotificationDossierIncomplet($admission);
                $this->notifierService->trigger($notif);
                $this->flashMessenger()->addSuccessMessage("$individu a bien été informé que son dossier d'admission est incomplet");
            } catch (RuntimeException $e) {
                throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::NOTIFICATION_DOSSIER_INCOMPLET."]",0,$e);
            }

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

    public function genererRecapitulatifAction(): void
    {
        $admission = $this->admissionService->getRepository()->findRequestedAdmission($this);
        /** @var Inscription $inscription */
        $inscription = $admission->getInscription()->first() ? $admission->getInscription()->first() : null;
        $logos = [];
        try {
            $site = $inscription && $inscription->getEtablissementInscription() ? $inscription->getEtablissementInscription()->getStructure() : null;
            $logos['site'] = $site ? $this->fichierStorageService->getFileForLogoStructure($site) : null;
        } catch (StorageAdapterException) {
            $logos['site'] = null;
        }
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            try {
                $logos['comue'] = $this->fichierStorageService->getFileForLogoStructure($comue->getStructure());
            } catch (StorageAdapterException) {
                $logos['comue'] = null;
            }
        }

        $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
        //Masquage des actions non voulues dans le circuit de signatures -> celles correspondant à la convention de formation doctorale
        $operations = $this->admissionOperationService->hideOperations($operations, TypeValidation::CODE_VALIDATIONS_CONVENTION_FORMATION_DOCTORALE);
        $export = $this->recapitulatifExporter;
        $export->setWatermark("CONFIDENTIEL");
        $export->setVars([
            'admission' => $admission,
            'logos' => $logos,
            'operations' => $operations
        ]);
        try {
            $export->export('SYGAL_admission_recapitulatif_' . $admission->getId() . ".pdf");
        } catch (MpdfException $e) {
            throw new RuntimeException("Un problème est survenu lors de la génération du pdf",0,$e);
        }
    }
}