<?php /** @noinspection PhpUnusedAliasInspection */

namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Document;
use Admission\Entity\Db\Etat;
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
use Application\Filter\IdifyFilter;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierServiceException;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
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
    use ValidationServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DocumentServiceAwareTrait;
    use VerificationServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use FichierStorageServiceAwareTrait;


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

        $admissions = $this->getAdmissionService()->getRepository()->findAll();
        return new ViewModel(['admissions' => $admissions]);
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
        $individu = $this->getIndividuService()->getRepository()->findRequestedIndividu($this);

        //Il faut que l'utilisateur existe, sinon on affiche une exception
        if ($individu === null) {
                throw new \UnicaenApp\Exception\RuntimeException("Individu spécifié introuvable");
        }

        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();
//        var_dump($data);
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
            $this->getAdmissionForm()->get('etudiant')->get('verificationEtudiant')->setObject($admission->getEtudiant()->first()->getVerificationEtudiant()->first());
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
//        var_dump($data);
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


    /**
     * @throws NotSupported
     */
    public function documentAction(): Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();
        /** @var Individu $individu */
        $individu = $this->getIndividuService()->getRepository()->findRequestedIndividu($this);
        //si l'individu est différent de celui en session, on vide les données en session
        if(array_key_exists('individu', $data["etudiant"])){
            $individuInSession = $data["etudiant"]['individu'];
            if((int)$individuInSession !== $individu->getId()){
                $this->multipageForm($this->getAdmissionForm())->clearSession();
            }
        };
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
        $documents = $this->getDocumentService()->getRepository()->findDocumentsByAdmission($admission);
        $docs = [];

        /** @var Document $document */
        foreach($documents as $document){
            $docs[$document->getFichier()->getNature()->getCode()] = ['libelle'=>$document->getFichier()->getNomOriginal(), 'televersement' => $document->getFichier()->getHistoModification()->format('d/m/Y H:i')];
        }

        $response->setVariable('dataForm', $data);
        $response->setVariable('documents', $docs);
        $response->setTemplate('admission/ajouter-document');

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
            //$this->enregistrerDocument($data);
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
            $rows = $this->getIndividuService()->getRepository()->findByText($term, $type);
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
        $individu=$this->getIndividuService()->getRepository()->findRequestedIndividu($this);
        return $this->getAdmissionService()->getRepository()->findOneByIndividu($individu);
    }

    public function enregistrerEtudiant($data, $admission){
        //Si le fieldset d'où l'on vient est etudiant,  on crée/enregistre ses données
        if ($data['_fieldset'] == "etudiant") {
            //Si l'etudiant ne possède pas de dossier d'admission, on lui crée puis associe un fieldset individu
            if ($admission === null) {
                try {
                    $individu = $this->getIndividuService()->getRepository()->findRequestedIndividu($this);
                    $this->getAdmissionForm()->get('etudiant')->bindValues($data['etudiant']);

                    /** @var Admission $admission */
                    $admission = $this->getAdmissionForm()->getObject();
                    $admission->setIndividu($individu);

                    /** @var Etat $enCours */
                    $enCours = $this->getEntityManager()->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS]);
                    $admission->setEtat($enCours);

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

                    /** @var Verification $verification */
                    $verification = $this->getVerificationService()->getRepository()->findOneByFinancement($financement);
                    if($verification === null){
                        /** @var Verification $verification */
                        $verification = $this->getAdmissionForm()->get('financement')->get('verificationFinancement')->getObject();
                        $verification->setFinancement($financement);
                        $this->getVerificationService()->create($verification);
                    }else{
                        $this->getAdmissionForm()->get('financement')->get('verificationFinancement')->setObject($verification);
                        $this->getAdmissionForm()->get('financement')->get('verificationFinancement')->bindValues($data['inscription']["verificationFinancement"]);

                        /** @var Verification $updatedVerification */
                        $updatedVerification = $this->getAdmissionForm()->get('financement')->get('verificationFinancement')->getObject();
                        $updatedVerification->setFinancement($financement);

                        $this->getVerificationService()->update($updatedVerification);
                    }

                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été modifiées avec succès.");
                }catch(\Exception $e){
                    $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                }
            }
        }
    }

    public function enregistrerDocumentAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Récupérez le fichier téléchargé via le gestionnaire de fichiers
            $file = $this->params()->fromFiles();
            foreach($file["document"] as $key=>$fileDetail){
                if (isset($fileDetail["error"]) && $fileDetail["error"] === UPLOAD_ERR_OK) {
                    $natureCode = $request->getPost('codeNatureFichier');
                    $nature = $this->getDocumentService()->getRepository()->fetchNatureFichier($natureCode);
                    if ($nature === null) {
                        return $this->createErrorResponse(422, "Nature de fichier spécifiée invalide");
                    }
                    $version = $this->versionFichierService->getRepository()->findOneByCode("VO");
                    if ($version === null) {
                        return $this->createErrorResponse(422, "Version de fichier spécifiée invalide");
                    }
                    try {
                        $individu = $request->getPost('individu');
                        $admission = $this->getAdmissionService()->getRepository()->findOneByIndividu($individu);

                        //Vérification de la validité du fichier
                        $fileValidity = $this->isValid($fileDetail);
                        if (!is_bool($fileValidity)) {
                            return $fileValidity;
                        }

                        $fileDetail = ["files" => $fileDetail];
                        $fichier = $this->fichierService->createFichiersFromUpload($fileDetail, $nature, $version);
                        $this->getDocumentService()->createDocumentFromUpload($admission, $fichier);
                        return new JsonModel(['success' => 'Document téléversé avec succès']);
                    } catch (\Exception $die) {
                        return $this->createErrorResponse(500, $die->getMessage());
                    }
                }
            }
        }
        return false;
    }

    public function supprimerDocumentAction()
    {
        $natureCode = $this->params()->fromQuery("codeNatureFichier");
        $nature = $this->getDocumentService()->getRepository()->fetchNatureFichier($natureCode);
        if ($nature === null) {
            return $this->createErrorResponse(422, "Nature de fichier spécifiée invalide");
        }
        try {
            $individu = $this->params()->fromQuery("individu");
            $admission = $this->getAdmissionService()->getRepository()->findOneByIndividu($individu);

            /** @var Document $document */
            $document = $this->getDocumentService()->getRepository()->findByAdmissionAndNature($admission, $nature);

            $this->getDocumentService()->delete($document);
            $this->flashMessenger()->addSuccessMessage("Document justificatif supprimé avec succès.");
            return new JsonModel(['success' => 'Document supprimé avec succès']);
        } catch (\Exception $die) {
            return $this->createErrorResponse(500, $die->getMessage());
        }
    }

    public function telechargerDocumentAction()
    {
        $natureCode = $this->params()->fromQuery('codeNatureFichier');
        $nature = $this->getDocumentService()->getRepository()->fetchNatureFichier($natureCode);
        if ($nature === null) {
            return $this->createErrorResponse(422, "Nature de fichier spécifiée invalide");
        }
        try {
            $individu = $this->params()->fromQuery('individu');
            $admission = $this->getAdmissionService()->getRepository()->findOneByIndividu($individu);
            /** @var Document $document */
            $document = $this->getDocumentService()->getRepository()->findByAdmissionAndNature($admission, $nature);
            try {
                $fichierContenu = $this->getDocumentService()->recupererDocumentContenu($document);
            } catch (FichierServiceException $e) {
                throw new \UnicaenApp\Exception\RuntimeException("Une erreur est survenue empêchant la création ", null, $e);
            }
            $this->fichierService->telechargerFichier($fichierContenu);
            return new JsonModel(['success' => 'Document téléchargé avec succès']);
        } catch (\Exception $die) {
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