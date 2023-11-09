<?php /** @noinspection PhpUnusedAliasInspection */

namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Verification;
use Admission\Form\Admission\AdmissionFormAwareTrait;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Provider\Template\MailTemplates;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Etudiant\EtudiantServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Admission\Service\Validation\ValidationServiceAwareTrait;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Controller\PaysController;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Controller\EtablissementController;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Structure\StructureServiceAwareTrait;


class AdmissionController extends AdmissionAbstractController {

    use StructureServiceAwareTrait;
    use DisciplineServiceAwareTrait;
    use NotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;
    use AdmissionFormAwareTrait;
    use EtudiantServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DocumentServiceAwareTrait;
    use VerificationServiceAwareTrait;

    public function indexAction(): ViewModel|Response
    {
        $request = $this->getRequest();
        $id = $request->getPost('individuId');

        if (!empty($id)) {
            $this->multipageForm($this->getAdmissionForm())->clearSession();
            return $this->redirect()->toRoute(
                'admission/ajouter',
                ['action' => "etudiant",
                    'individu' => $id],
                [],
                true);
        }
        return new ViewModel();
    }

    public function ajouterAction(): Response
    {
        return $this->multipageForm($this->getAdmissionForm())
            ->setUsePostRedirectGet()
            ->setReuseRequestQueryParams() // la redir vers la 1ere étape conservera les nom, prenom issus de la recherche
            ->start(); // réinit du plugin et redirection vers la 1ère étape
    }

    /**
     * @throws NotSupported
     */
    public function etudiantAction(): Response|ViewModel
    {
        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);

        //Il faut que l'utilisateur existe, sinon on affiche une exception
        if ($individu === null) {
                throw new \UnicaenApp\Exception\RuntimeException("Individu spécifié introuvable");
        }

        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();

        //si l'individu est différent de celui en session, on vide les données en session
        if(array_key_exists('individu', $data["etudiant"])){
            $individuInSession = $data["etudiant"]['individu'];
            if((int)$individuInSession !== $individu->getId()){
                $this->multipageForm($this->getAdmissionForm())->clearSession();
            }
        };

        $admission = $this->getAdmission();
        if(!empty($admission)) {
            $this->getAdmissionForm()->bind($admission);
        }

        if ($response instanceof Response) {
            return $response;
        }
        $response->setVariable('individu', $individu);
        $response->setTemplate('admission/ajouter-etudiant');

        return $response;
    }

    /**
     * @throws NotSupported
     */
    public function inscriptionAction(): Response|ViewModel
    {
        //Partie Informations sur l'inscription
        /** @see AdmissionController::rechercherIndividuAction() */
        $this->getAdmissionForm()->get('inscription')->setUrlDirecteurThese($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true));
        /** @see AdmissionController::rechercherIndividuAction() */
        $this->getAdmissionForm()->get('inscription')->setUrlCoDirecteurThese($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true));
        /** @see EtablissementController::rechercherAction() */
        $this->getAdmissionForm()->get('inscription')->setUrlEtablissement($this->url()->fromRoute('etablissement/rechercher', [], ["query" => []], true));

        $disciplines = $this->getDisciplineService()->getDisciplinesAsOptions('code','ASC','code');
        $this->getAdmissionForm()->get('inscription')->setSpecialites($disciplines);

        $ecoles = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', false);
        $this->getAdmissionForm()->get('inscription')->setEcolesDoctorales($ecoles);

        $unites = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', false);
        $this->getAdmissionForm()->get('inscription')->setUnitesRecherche($unites);

        //Partie Spécifités envisagées
        /** @see PaysController::rechercherPaysAction() */
        $this->getAdmissionForm()->get('inscription')->setUrlPaysCoTutelle($this->url()->fromRoute('pays/rechercher-pays', [], ["query" => []], true));

        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();
        /** @var Admission $admission */
        $admission = $this->getAdmission();

        //Enregistrement des informations de l'Etudiant
        $this->enregistrerEtudiant($data, $admission);

        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-inscription');

        return $response;
    }

    public function financementAction() : Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();

        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if(!empty($admission)) {
            $this->getAdmissionForm()->bind($admission);
            //Enregistrement des informations concernant l'inscription
            $this->enregistrerInscription($data, $admission);
        }

        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-financement');

        return $response;
    }


    public function validationAction(): Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();

        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if(!empty($admission)){
            $this->getAdmissionForm()->bind($admission);
            //Enregistrement des informations de Financement
            $this->enregistrerFinancement($data, $admission);
        }

        if ($response instanceof Response) {
            return $response;
        }

        $response->setVariable('dataForm', $data);
        $response->setTemplate('admission/ajouter-validation');

        return $response;
    }

    public function annulerAction(): Response
    {
        $this->multipageForm()->clearSession();

        // todo: fournir une page de demande de confirmation d'annulation via le plugin multipageForm() ?
        return $this->redirect()->toRoute('admission/ajouter/etudiant');
    }

    public function confirmerAction()
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();

        /** @var Admission $admission */
        $admission = $this->getAdmission();

        if(!empty($admission)){
            //Enregistrement des Documents ajoutéss
            $this->enregistrerDocument($data);
        }

        if ($response instanceof Response) {
            return $response;
        }

        $response->setVariable('dataForm', $data);
        $response->setTemplate('admission/ajouter-confirmer');

        return $response;
    }

    public function enregistrerAction()
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $this->multipageForm($this->getAdmissionForm())->clearSession();

        if ($response instanceof Response) {
            return $response;
        }
        return $response;
    }

    public function envoyerMailAction(): Response
    {
        try {
            $notif = $this->notificationFactory->createNotificationEnvoyerMail();
            $this->notifierService->trigger($notif);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'envoi du mail [".MailTemplates::ENVOYER_MAIL."]",0,$e);
        }

        return $this->redirect()->toRoute('home', [], [], true );
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

    public function enregistrerEtudiant($data, $admission){
        //Si le fieldset d'où l'on vient est etudiant,  on crée/enregistre ses données
        if ($data['_fieldset'] == "etudiant") {
            //Si l'etudiant ne possède pas de dossier d'admission, on lui crée puis associe un fieldset individu
            if ($admission === null) {
                try {
                    $individu = $this->individuService->getRepository()->findRequestedIndividu($this);
                    $this->getAdmissionForm()->get('etudiant')->bindValues($data['etudiant']);

                    /** @var Admission $admission */
                    $admission = $this->getAdmissionForm()->getObject();
                    $admission->setIndividu($individu);

                    /** @var Etudiant $etudiant */
                    $etudiant = $this->getAdmissionForm()->get('etudiant')->getObject();
                    $etudiant->setAdmission($admission);
                    $this->getEtudiantService()->create($etudiant, $admission);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (\Exception $e) {
                    var_dump($e);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente n'ont pas pu être enregistrées.");
                }
            } else {
                //si le dossier d'admission existe, on met à jour l'entité Etudiant
                try {
                    /** @var Etudiant $etudiant */
                    $etudiant = $admission->getEtudiant()->first();

                    //Mise à jour de l'entité (a remettre)
                    $this->getAdmissionForm()->get('etudiant')->setObject($etudiant);
                    $this->getAdmissionForm()->get('etudiant')->bindValues($data['etudiant']);
                    $etudiant = $this->getAdmissionForm()->get('etudiant')->getObject();
                    $this->getEtudiantService()->update($etudiant);

                    if($this->isAllowed(AdmissionPrivileges::getResourceId(AdmissionPrivileges::ADMISSION_VERIFIER))){
                        /** @var Verification $verification */
                        $verification = $this->getVerificationService()->getRepository()->findOneByEtudiant($etudiant);
                        if($verification === null){
                            /** @var Verification $verification */
                            $verification = $this->getAdmissionForm()->get('etudiant')->get('verificationEtudiant')->getObject();
                            $verification->setEtudiant($etudiant);
                            $this->getVerificationService()->create($verification);
                        }else{
                            $this->getAdmissionForm()->get('etudiant')->get('verificationEtudiant')->setObject($verification);
                            $this->getAdmissionForm()->get('etudiant')->get('verificationEtudiant')->bindValues($data['etudiant']["verificationEtudiant"]);

                            /** @var Verification $updatedVerification */
                            $updatedVerification = $this->getAdmissionForm()->get('etudiant')->get('verificationEtudiant')->getObject();
                            $updatedVerification->setEtudiant($etudiant);

                            $this->getVerificationService()->update($updatedVerification);
                        }
                    }
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été modifiées avec succès.");
                } catch (\Exception $e) {
                    var_dump($e);
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
            }
        }
        $this->getAdmissionForm()->bind($admission);
    }

    public function enregistrerInscription($data, $admission){
        /** @var Inscription $inscription */
        $inscription = $this->getInscriptionService()->getRepository()->findOneByAdmission($admission);

        //Si le fieldset d'où l'on vient est inscription,  on crée/enregistre ses données
        if ($data['_fieldset'] == "inscription") {
            //Si le fieldest Inscription n'était pas encore en BDD
            if (!$inscription instanceof Inscription) {
                try {
                    $this->getAdmissionForm()->get('inscription')->bindValues($data['inscription']);
                    /** @var Inscription $inscription */
                    $inscription = $this->getAdmissionForm()->get('inscription')->getObject();
                    $inscription->setAdmission($admission);

                    $this->getInscriptionService()->create($inscription);

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                }catch(\Exception $e){
//                        var_dump($e);
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                }
            } else {
                try {
                    //Mise à jour de l'entité
                    $this->getAdmissionForm()->get('inscription')->setObject($inscription);
                    $this->getAdmissionForm()->get('inscription')->bindValues($data['inscription']);
                    /** @var Inscription $inscription */
                    $inscription = $this->getAdmissionForm()->get('inscription')->getObject();
                    $this->getInscriptionService()->update($inscription);

                    /** @var Verification $verification */
                    $verification = $this->getVerificationService()->getRepository()->findOneByInscription($inscription);
                    if($verification === null){
                        /** @var Verification $verification */
                        $verification = $this->getAdmissionForm()->get('inscription')->get('verificationInscription')->getObject();
                        $verification->setInscription($inscription);
                        $this->getVerificationService()->create($verification);
                    }else{
                        $this->getAdmissionForm()->get('inscription')->get('verificationInscription')->setObject($verification);
                        $this->getAdmissionForm()->get('inscription')->get('verificationInscription')->bindValues($data['inscription']["verificationInscription"]);

                        /** @var Verification $updatedVerification */
                        $updatedVerification = $this->getAdmissionForm()->get('inscription')->get('verificationInscription')->getObject();
                        $updatedVerification->setInscription($inscription);

                        $this->getVerificationService()->update($updatedVerification);
                    }

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été modifiées avec succès.");
                }catch(\Exception $e){
//                        var_dump($e);
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
            }
        }
    }

    public function enregistrerFinancement($data, $admission){
        /** @var Financement $financement */
        $financement = $this->getFinancementService()->getRepository()->findOneByAdmission($admission);

        //Si le fieldset d'où l'on vient est financement,  on crée/enregistre ses données
        if($data['_fieldset'] == "financement") {
            //Si le fieldest Financement n'était pas encore en BDD
            if (!$financement instanceof Financement) {
                try {
                    $this->getAdmissionForm()->get('financement')->bindValues($data['financement']);
                    /** @var Financement $financement */
                    $financement = $this->getAdmissionForm()->get('financement')->getObject();
                    $financement->setAdmission($admission);

                    $this->getFinancementService()->create($financement);

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                }catch(\Exception $e){
                    $this->flashMessenger()->addErrorMessage("Échec de l'enregistrement des informations.");
                }
            } else {
                try {
                    $this->getAdmissionForm()->get('financement')->setObject($financement);
                    $this->getAdmissionForm()->get('financement')->bindValues($data['financement']);
                    /** @var Financement $financement */
                    $financement = $this->getAdmissionForm()->get('financement')->getObject();
                    $this->getFinancementService()->update($financement);

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été modifiées avec succès.");
                }catch(\Exception $e){
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
            }
        }
    }

    public function enregistrerDocument($data){
        if($data['_fieldset'] == "validation") {
            //Si le fieldest Validation n'était pas encore en BDD
            //            if (!$validationFieldset instanceof Financement) {
            //                $this->getAdmissionForm()->get('validation')->bindValues($data['validation']);
            //                $entity = $this->getAdmissionForm()->get('validation')->getObject();
            //                $entity->setAdmission($admission);
            //
            //                $this->validationService->create($entity, $admission);
            //            } else {
            //                $this->getAdmissionForm()->get('validation')->setObject($validationFieldset);
            //                $this->getAdmissionForm()->get('validation')->bindValues($data['validation']);
            //                $entity = $this->getAdmissionForm()->get('validation')->getObject();
            //                $this->validationService->update($entity);
            //            }
            /** @var NatureFichier $nature */
            //            $nature = $this->natureFichierService->getRepository()->find($data['nature']);
            //
            ////            $files = $request->getFiles()->toArray();
            //            $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
            //            $this->fichierService->saveFichiers($fichiers);
            //            $this->documentService->addDocument($admission, $nature, $fichiers[0]);

        }
    }
}