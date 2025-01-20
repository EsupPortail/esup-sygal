<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\RouteMatch;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use Formation\Form\Formation\FormationFormAwareTrait;
use Formation\Service\Formation\FormationServiceAwareTrait;
use Formation\Service\Module\ModuleServiceAwareTrait;
use Formation\Service\Notification\FormationNotificationFactoryAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationController extends AbstractController
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use ModuleServiceAwareTrait;
    use SessionServiceAwareTrait;
    use FormationFormAwareTrait;
    use NotifierServiceAwareTrait;
    use FormationNotificationFactoryAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;

    use EtablissementServiceAwareTrait;

    public function afficherAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $verifierDatePublication = (bool)$this->userContextService->getSelectedRoleDoctorant();
        $sessions = $this->getSessionService()->getRepository()->fetchSessionsByFormation($formation, 'id', 'asc', true, $verifierDatePublication);
        $anneeUnivCourante = $this->anneeUnivService->courante();

        $sessionsAvecAnneeUniv = [];
        /** @var Session $session */
        foreach($sessions as $session){
            $premiereAnnee = null;
            if($session->getDateDebut()){
                $anneeUniv = $this->anneeUnivService->fromDate($session->getDateDebut());
                $premiereAnnee = $anneeUniv->getPremiereAnnee();
            }
            $sessionsAvecAnneeUniv[] = array("session" => $session, "anneeUniv" => $premiereAnnee ?? $anneeUnivCourante->getPremiereAnnee());
        }

        return new ViewModel([
            'formation' => $formation,
            'sessions' => $sessionsAvecAnneeUniv,
            'anneeUnivCourante' => $anneeUnivCourante,
            'anneesUniv' => $this->formationService->getAnneesUnivAsOptions($sessions),
            'urlFichierPlugin' => $this->urlFichier(),
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $formation = new Formation();

        if ($module !== null) $formation->setModule($module);

        $form = $this->getFormationForm();
        $form->setUrlResponsable($this->url()->fromRoute('individu/rechercher', [], [], true));
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/ajouter', ['module' => ($module)?$module->getId():null], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $files = $request->getFiles()->toArray();
                if(isset($files['fiche']['size']) && $files['fiche']['size'] !== 0){
                    /** @var NatureFichier $nature */
                    $nature = $this->natureFichierService->getRepository()->findOneBy(['code' => \Formation\Provider\NatureFichier\NatureFichier::CODE_FICHE_FORMATION]);
                    $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
                    $this->fichierService->saveFichiers($fichiers);
                    $formation->setFiche(array_pop($fichiers));
                }
                $this->getFormationService()->create($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $form = $this->getFormationForm();
        $form->setUrlResponsable($this->url()->fromRoute('individu/rechercher', [], [], true));
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/modifier', [], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                /** @var NatureFichier $nature */
                $nature = $this->natureFichierService->getRepository()->findOneBy(['code' => \Formation\Provider\NatureFichier\NatureFichier::CODE_FICHE_FORMATION]);

                $files = $request->getFiles()->toArray();
                if(isset($files['fiche']['size']) && $files['fiche']['size'] !== 0){
                    //Suppression de l'ancienne fiche, si une déjà présente
                    if($formation->getFiche()){
                        $oldFiche = $formation->getFiche();
                        $formation->setFiche(null);
                        $this->getFormationService()->update($formation);
                        $this->fichierService->supprimerFichiers([$oldFiche]);
                    }
                    $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
                    $this->fichierService->saveFichiers($fichiers);
                    $formation->setFiche(array_pop($fichiers));
                }

                $this->getFormationService()->update($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une formation",
            'form' => $form,
            'urlFichierPlugin' => $this->urlFichier(),
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $this->getFormationService()->historise($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/formation');

    }

    public function restaurerAction() : Response
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $this->getFormationService()->restore($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/formation');
    }

    public function supprimerAction()
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getFormationService()->delete($formation);
            exit();
        }

        $vm = new ViewModel();
        if ($formation !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression d'une formation #" . $formation->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formation/supprimer', ["formation" => $formation->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function supprimerFicheAction(): Response|ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();
        $fichierId = $routeMatch->getParam("fichier");
        $fichier = $this->fichierService->getRepository()->find($fichierId);

        $result = $this->confirm()->execute();

        // si un tableau est retourné par le plugin Confirm, l'opération a été confirmée
        if (is_array($result)) {
            $formation->setFiche(null);
            $this->getFormationService()->update($formation);

            $this->fichierService->supprimerFichiers([$fichier]);

            $this->flashMessenger()->addSuccessMessage("La fiche de la formation {$formation->getLibelle()} supprimée avec succès.");
            if($redirectUrl = $this->params()->fromQuery('redirect')){
                return $this->redirect()->toUrl($redirectUrl);
            }else{
                return $this->redirect()->toRoute('formation/formation/modifier', ['formation' => $formation->getId()], [], true);
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
}