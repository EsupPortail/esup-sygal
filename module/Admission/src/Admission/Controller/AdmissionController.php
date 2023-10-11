<?php /** @noinspection PhpUnusedAliasInspection */

namespace Admission\Controller;

use Admission\Entity\Db\Individu;
use Admission\Form\Admission\AdmissionForm;
use Admission\Form\Admission\AdmissionFormAwareTrait;
use Admission\Hydrator\IndividuHydrator;
use Admission\Provider\Template\MailTemplates;
use Admission\Service\Individu\IndividuServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Http\Response;
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

    public function ajouterAction(): Response
    {
        return $this->multipageForm($this->getEtudiantForm())
            ->setUsePostRedirectGet()
            ->setReuseRequestQueryParams() // la redir vers la 1ere étape conservera les nom, prenom issus de la recherche
            ->start(); // réinit du plugin et redirection vers la 1ère étape
    }

    public function etudiantEnregistrerAction(){
        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();
        $this->getEtudiantForm()->bindValues($data);
//        var_dump($data);

        $request = $this->getRequest();
//        var_dump($request->isPost());
        if ($request->isPost()) {
            $postData = $request->getPost();
            var_dump($postData); // Affichez les données pour déboguer
        }
    }

    public function etudiantAction(): Response|ViewModel
    {
        $this->getEtudiantForm()->get('etudiant')->setUrlPaysNationalite($this->url()->fromRoute('pays/rechercher-pays', [], [], true));
        $this->getEtudiantForm()->get('etudiant')->setUrlNationalite($this->url()->fromRoute('pays/rechercher-nationalite', [], [], true));

//        var_dump($this->params()->fromPost()[MultipageFormNavFieldset::NAME] == MultipageFormNavFieldset::NAME_NEXT);
        var_dump($this->params()->fromPost());
        $response = $this->processMultipageForm($this->getEtudiantForm());
        $data = $this->multipageForm($this->getEtudiantForm())->getFormSessionData();

//
        $individu = $this->getEtudiantForm()->getObject();
        var_dump($individu);
//        $this->individuService->create($individu);
//        var_dump($this->getEtudiantForm()->get('etudiant'));
        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-etudiant');

        return $response;
    }

    public function inscriptionAction(): Response|ViewModel
    {
        var_dump($this->params()->fromPost());

        //Partie Informations sur l'inscription
        $this->getEtudiantForm()->get('inscription')->setUrlDirecteurThese($this->url()->fromRoute('utilisateur/rechercher-individu', [], ["query" => []], true));
        $this->getEtudiantForm()->get('inscription')->setUrlCoDirecteurThese($this->url()->fromRoute('utilisateur/rechercher-individu', [], ["query" => []], true));
        $this->getEtudiantForm()->get('inscription')->setUrlEtablissement($this->url()->fromRoute('etablissement/rechercher', [], ["query" => []], true));
        $disciplines = $this->getDisciplineService()->getDisciplinesAsOptions();
        $this->getEtudiantForm()->get('inscription')->setDisciplines($disciplines);
        $ecoles = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle', false);
        $this->getEtudiantForm()->get('inscription')->setEcolesDoctorales($ecoles);
        $unites = $this->getStructureService()->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, 'libelle', false);
        $this->getEtudiantForm()->get('inscription')->setUnitesRecherche($unites);

        //Partie Spécifités envisagées
        $this->getEtudiantForm()->get('inscription')->setUrlPaysCoTutelle($this->url()->fromRoute('pays/rechercher-pays', [], ["query" => []], true));

        $response = $this->processMultipageForm($this->getEtudiantForm());
        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-inscription');

        return $response;
    }

    public function financementAction() : Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getEtudiantForm());
        if ($response instanceof Response) {
            return $response;
        }

        $response->setTemplate('admission/ajouter-financement');

        return $response;
    }

    public function validationAction(): Response|ViewModel
    {
        $response = $this->processMultipageForm($this->getEtudiantForm());
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
}