<?php /** @noinspection PhpUnusedAliasInspection */

namespace Admission\Controller;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Individu;
use Admission\Entity\Db\Inscription;
use Admission\Form\Admission\AdmissionForm;
use Admission\Form\Admission\AdmissionFormAwareTrait;
use Admission\Hydrator\IndividuHydrator;
use Admission\Provider\Template\MailTemplates;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Individu\IndividuServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Admission\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Doctrine\ORM\Exception\NotSupported;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
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

    public function ajouterAction(): Response
    {
        return $this->multipageForm($this->getEtudiantForm())
            ->setUsePostRedirectGet()
            ->setReuseRequestQueryParams() // la redir vers la 1ere étape conservera les nom, prenom issus de la recherche
            ->start(); // réinit du plugin et redirection vers la 1ère étape
    }

    public function etudiantAction(): Response|ViewModel
    {
        $this->getEtudiantForm()->get('etudiant')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
        $this->getEtudiantForm()->get('etudiant')->setUrlNationalite($this->url()->fromRoute('pays/rechercher-nationalite', [], [], true));

//        var_dump($this->params()->fromPost()[MultipageFormNavFieldset::NAME] == MultipageFormNavFieldset::NAME_NEXT);
//        var_dump($this->params()->fromPost());
        $response = $this->processMultipageForm($this->getEtudiantForm());
        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();

//        $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
//        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
//        $individuFieldset = $this->individuAdmissionService->getRepository()->findOneByAdmission($admission->getId());
//
//        if($individuFieldset instanceof Individu){
//            $this->getEtudiantForm()->get('etudiant')->setObject($individuFieldset);
//        }else{
//            var_dump("le fieldset Individu   n'est pas initialisé");
//        }

        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-etudiant');

        return $response;
    }

    /**
     * @throws NotSupported
     */
    public function inscriptionAction(): Response|ViewModel
    {
//        var_dump($this->params()->fromPost());
//        var_dump($this->params()->fromRoute('individu'));
        //Partie Informations sur l'inscription
        $this->getEtudiantForm()->get('inscription')->setUrlDirecteurThese($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true));
        $this->getEtudiantForm()->get('inscription')->setUrlCoDirecteurThese($this->url()->fromRoute('admission/rechercher-individu', [], ["query" => []], true));
        $this->getEtudiantForm()->get('inscription')->setUrlEtablissement($this->url()->fromRoute('etablissement/rechercher', [], ["query" => []], true));
        $disciplines = $this->getDisciplineService()->getDisciplinesAsOptions('code','ASC','code');
        $this->getEtudiantForm()->get('inscription')->setSpecialites($disciplines);
        $ecoles = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', false);
        $this->getEtudiantForm()->get('inscription')->setEcolesDoctorales($ecoles);
        $unites = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', false);
        $this->getEtudiantForm()->get('inscription')->setUnitesRecherche($unites);
//        var_dump($this->params()->fromPost());
//        die();
        //Partie Spécifités envisagées
        $this->getEtudiantForm()->get('inscription')->setUrlPaysCoTutelle($this->url()->fromRoute('pays/rechercher-pays', [], ["query" => []], true));

        $response = $this->processMultipageForm($this->getEtudiantForm());
//        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();
////        var_dump($data);
//        $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
//        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
//
//        //Si l'individu ne possède pas de dossier d'admission, on lui crée puis associe un fieldset étudiant
//        if($admission === null){
//            try {
//                $this->getEtudiantForm()->get('etudiant')->bindValues($data['etudiant']);
//                /** @var Admission $admission */
//                $admission = $this->getEtudiantForm()->getObject();
//                $admission->setIndividuId($individu);
//
//                $entity = $this->getEtudiantForm()->get('etudiant')->getObject();
//                $entity->setAdmission($admission);
//
//                $this->individuAdmissionService->create($entity, $admission);
//            }catch (\Exception $e) {
//                var_dump($e);
//            }
//        }else{
////            var_dump("l'individu possède déjà un dossier d'admission");
//            //Faire l'update du fieldset étudiant
//
//            //Vérifier que le fieldset inscription existe et si oui : populate les valeurs avec la BDD
////            var_dump($admission);
//            $inscriptionFieldset = $this->inscriptionService->getRepository()->findOneByAdmission($admission->getId());
//            var_dump($inscriptionFieldset);
//            if($inscriptionFieldset instanceof Inscription){
//                $this->getEtudiantForm()->get('inscription')->setObject($inscriptionFieldset);
//            }else{
//                var_dump("le fieldset Inscription n'est pas initialisé");
//            }
//        }

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
//        var_dump($this->getRequest()->getPost());
//        die();
        $response = $this->processMultipageForm($this->getEtudiantForm());
        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();

//        $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
//        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
//        $inscriptionFieldset = $this->inscriptionService->getRepository()->findOneByAdmission($admission->getId());
//
//        //Si le fieldest Financement n'était pas encore en BDD
//        if(!$inscriptionFieldset instanceof Inscription){
//            $this->getEtudiantForm()->get('inscription')->bindValues($data['inscription']);
//            $entity = $this->getEtudiantForm()->get('inscription')->getObject();
//            $entity->setAdmission($admission);
//
//            $this->inscriptionService->create($entity, $admission);
//        }

        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-financement');

        return $response;
    }

    public function validationAction(): Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getEtudiantForm());
        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();
        var_dump($this->params()->fromPost());
//        die();
    //        $individu=$this->individuService->getRepository()->findRequestedIndividu($this);
    //        $admission = $this->admissionService->getRepository()->findOneByIndividu($individu);
    //
    //        $financementFieldset = $this->financementService->getRepository()->findOneByAdmission($admission->getId());
    //
    //        //Si le fieldest Inscription n'était pas encore en BDD
    //        if(!$financementFieldset instanceof Financement){
    //            $this->getEtudiantForm()->get('financement')->bindValues($data['financement']);
    //            $entity = $this->getEtudiantForm()->get('financement')->getObject();
    //            $entity->setAdmission($admission);
    //
    //            $this->financementService->create($entity, $admission);
    //        }

        if ($response instanceof Response) {
            return $response;
        }

        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();

        $response->setVariable('data_form', $data);
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
        $response = $this->multipageForm($this->getEtudiantForm())->process();
        if ($response instanceof Response) {
            return $response;
        }
        return array('form' => $this->getEtudiantForm());
    }

    public function enregistrerAction()
    {
        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();
        var_dump($data);

        // ...
        // enregistrement en base de données (par exemple)
        // ...
        return "";
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

    public function saveData($fieldsetName, $service, $data){
//        if (isset($data[$fieldsetName]['nationalite']['label'])) {
//            $data[$fieldsetName]['nationalite'] = $data[$fieldsetName]['nationalite']['label'];
//        }
        $this->getEtudiantForm()->get($fieldsetName)->bindValues($data[$fieldsetName]);

        if(!$service->getRepository()->findIfCurrentUserHasAlreadyAdmission()){
            try {
                $admission = new Admission();

                $entity = $this->getEtudiantForm()->get('etudiant')->getObject();
                $entity->setAdmission($admission);
                var_dump($entity);
                $service->getEntityManager()->persist($admission);
                $service->create($entity);
            }
            catch (\Exception $e) {

            }
        }else{

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
                    'prenoms'=>$row['email'],
                    'nom' => $row['nom_usuel']
                );
                $result[] = array(
                    'id' => $row['id'], // identifiant unique de l'item
                    'label' => $label,     // libellé de l'item
                    'extra' => $extra,     // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }
}