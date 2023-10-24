<?php /** @noinspection PhpUnusedAliasInspection */

namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Individu;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Repository\ValidationRepository;
use Admission\Form\Admission\AdmissionForm;
use Admission\Form\Admission\AdmissionFormAwareTrait;
use Admission\Hydrator\IndividuHydrator;
use Admission\Provider\Template\MailTemplates;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Individu\IndividuServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Admission\Service\Validation\ValidationServiceAwareTrait;
use Application\Controller\PaysController;
use Application\Controller\UtilisateurController;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Controller\EtablissementController;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenApp\Controller\Plugin\MultipageFormPlugin;
use UnicaenApp\Form\Fieldset\MultipageFormNavFieldset;


class AdmissionController extends AdmissionAbstractController {

    use StructureServiceAwareTrait;
    use DisciplineServiceAwareTrait;
    use NotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;
    use AdmissionFormAwareTrait;
    use IndividuServiceAwareTrait;
    use \Individu\Service\IndividuServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use DocumentServiceAwareTrait;

    public function indexAction(): ViewModel|Response
    {
        $request = $this->getRequest();
        $id = $request->getPost('individuId');

        if (!empty($id)) {
            $this->multipageForm($this->getAdmissionForm())->clearSession();
            return $this->redirect()->toRoute(
                'admission/ajouter',
                ['action' => "individu",
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

    public function individuAction(): Response|ViewModel
    {
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);

        //Il faut que l'utilisateur existe, sinon on affiche une exception
        if ($individu === null) {
                throw new \UnicaenApp\Exception\RuntimeException("Individu spécifié introuvable");
//                return $this->redirect()->toRoute('admission',[],[],true);
        }


        $this->getAdmissionForm()->get('individu')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
        $this->getAdmissionForm()->get('individu')->setUrlNationalite($this->url()->fromRoute('pays/rechercher-nationalite', [], [], true));

        $response = $this->processMultipageForm($this->getAdmissionForm());
        $admission = $this->getAdmission();

        if(!empty($admission)) {
            $this->getAdmissionForm()->bind($admission);
        }

        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-individu');

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
        $admission = $this->getAdmission();

        //Si le fieldset d'où l'on vient est individu,  on crée/enregistre ses données
        if ($data['_fieldset'] == "individu") {
            //Si l'individu ne possède pas de dossier d'admission, on lui crée puis associe un fieldset individu
            if ($admission == null) {
                try {
                    $individu = $this->individuService->getRepository()->findRequestedIndividu($this);
                    $this->getAdmissionForm()->get('individu')->bindValues($data['individu']);

                    /** @var Admission $admission */
                    $admission = $this->getAdmissionForm()->getObject();
                    $admission->setIndividuId($individu);

                    $entity = $this->getAdmissionForm()->get('individu')->getObject();
                    $entity->setAdmission($admission);

                    $this->individuAdmissionService->create($entity, $admission);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                } catch (\Exception $e) {
//                    var_dump($e);
                }
            } else {
                //si le dossier d'admission existe, on met à jour l'entité Individu
                try {
                    //Mise à jour de l'entité
                    $this->getAdmissionForm()->get('individu')->setObject($admission->getIndividu()->first());

                    $this->getAdmissionForm()->get('individu')->bindValues($data['individu']);
                    $entity = $this->getAdmissionForm()->get('individu')->getObject();
                    $this->individuAdmissionService->update($entity);
                    $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été modifiées avec succès.");
                } catch (\Exception $e) {
//                    var_dump($e);
                    $this->flashMessenger()->addErrorMessage()("Échec de la modification des informations.");
                }
            }

            //            $admission = $this->getAdmissionForm();
            //            $admission->bindValues($data);
            //            $admissionObject = $admission->getObject();
            //            $this->admissionService->ajouter($admissionObject);

        }
        $this->getAdmissionForm()->bind($admission);

        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-inscription');

        return $response;
    }

    /**
     * @throws NotSupported
     */
    public function financementAction() : Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();

        $admission = $this->getAdmission();

        if(!empty($admission)) {
            $inscriptionFieldset = $this->inscriptionService->getRepository()->findOneByAdmission($admission->getId());
            $this->getAdmissionForm()->bind($admission);

            //Si le fieldset d'où l'on vient est inscription,  on crée/enregistre ses données
            if ($data['_fieldset'] == "inscription") {
                //Si le fieldest Inscription n'était pas encore en BDD
                if (!$inscriptionFieldset instanceof Inscription) {
                    try {
                        $this->getAdmissionForm()->get('inscription')->bindValues($data['inscription']);
                        $entity = $this->getAdmissionForm()->get('inscription')->getObject();
                        $entity->setAdmission($admission);

                        $this->inscriptionService->create($entity, $admission);
                        $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été ajoutées avec succès.");
                    }catch(\Exception $e){
//                        var_dump($e);
                        $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                    }
                } else {
                    try {
                        //Mise à jour de l'entité
                        $this->getAdmissionForm()->get('inscription')->setObject($inscriptionFieldset);
                        $this->getAdmissionForm()->get('inscription')->bindValues($data['inscription']);
                        $entity = $this->getAdmissionForm()->get('inscription')->getObject();
                        $this->inscriptionService->update($entity);
                        $this->flashMessenger()->addSuccessMessage("Les informations concernant l'étape précédente ont été modifiées avec succès.");
                    }catch(\Exception $e){
//                        var_dump($e);
                        $this->flashMessenger()->addErrorMessage("Échec de la modification des informations.");
                    }

                }
            }
        }else{

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
    public function validationAction(): Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();

        $admission = $this->getAdmission();
        if(!empty($admission)){
            $financementFieldset = $this->financementService->getRepository()->findOneByAdmission($admission->getId());

            //Si le fieldset d'où l'on vient est financement,  on crée/enregistre ses données
            if($data['_fieldset'] == "financement") {
                //Si le fieldest Financement n'était pas encore en BDD
                if (!$financementFieldset instanceof Financement) {
                    $this->getAdmissionForm()->get('financement')->bindValues($data['financement']);
                    $entity = $this->getAdmissionForm()->get('financement')->getObject();
                    $entity->setAdmission($admission);

                    $this->financementService->create($entity, $admission);
                } else {
                    $this->getAdmissionForm()->get('financement')->setObject($financementFieldset);
                    $this->getAdmissionForm()->get('financement')->bindValues($data['financement']);
                    $entity = $this->getAdmissionForm()->get('financement')->getObject();
                    $this->financementService->update($entity);
//                $admission = $this->getAdmissionForm()->setObject($admission);
//                $admission->bindValues($data);
//                $admissionObject = $admission->getObject();
//                $this->admissionService->modifier($admissionObject);
                }
            }
        }else{
//            return $this->redirect()->toRoute('admission', ['action' => "individu", "individu" => "752883"], [], true);
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
        return $this->redirect()->toRoute('admission/ajouter/individu');
    }

    public function confirmerAction()
    {
        $response = $this->processMultipageForm($this->getAdmissionForm());
        $data = $this->multipageForm($this->getAdmissionForm())->getFormSessionData();
//        var_dump($data);
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);
        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);

        $validationFieldset = $this->validationService->getRepository()->findOneByAdmission($admission->getId());

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
            $this->documentService->addDocument($admission, $nature, $fichiers[0]);

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

    public function getAdmission(): Admission|null
    {
        $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
        return $this->admissionService->getRepository()->findOneByIndividu($individu);
    }
}