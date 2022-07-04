<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\These;
use Formation\Service\Formation\FormationServiceAwareTrait;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use Laminas\Http\Response;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\File\FileServiceAwareTrait;
use DateTime;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Formation\Form\Session\SessionFormAwareTrait;
use Formation\Service\Exporter\Emargement\EmargementExporter;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Notification\NotificationServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use UnicaenApp\View\Model\CsvModel;

class SessionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use EtablissementServiceAwareTrait;
    use FileServiceAwareTrait;
    use FormationServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use NotificationServiceAwareTrait;
    use PresenceServiceAwareTrait;
    use SessionServiceAwareTrait;

    use SessionFormAwareTrait;

    private PhpRenderer $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function afficherAction()
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $presences = $this->getPresenceService()->getRepository()->findPresencesBySession($session);
        $dictionnaire = [];
        foreach ($presences as $presence) {
            $dictionnaire[$presence->getSeance()->getId()][$presence->getInscription()->getId()] = $presence;
        }

        return new ViewModel([
            'session' => $session,
            'presences' => $dictionnaire,
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $session = new Session();
        $session->setFormation($formation);
        $session = $this->getSessionService()->setValeurParDefaut($session);

        $form = $this->getSessionForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/ajouter', ['module' => $formation->getId()], [], true));
        $form->bind($session);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionService()->create($session);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une session de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $form = $this->getSessionForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/modifier', ['session' => $session->getId()], [], true));
        $form->bind($session);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionService()->update($session);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une session de formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $this->getSessionService()->historise($session);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session');
    }

    public function restaurerAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $this->getSessionService()->restore($session);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session');
    }

    public function supprimerAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getSessionService()->delete($session);
            exit();
        }

        $vm = new ViewModel();
        if ($session !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la session #" . $session->getIndex(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/session/supprimer', ["module" => $session->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function changerEtatAction()
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        /**@var Etat $etat */
        $etat = $this->getEntityManager()->getRepository(Etat::class)->getRequestedEtat($this);

        if ($etat) {
            $retour = $this->params()->fromQuery('retour');
            $session->setEtat($etat);
            $this->getSessionService()->update($session);

            if ($retour) return $this->redirect()->toUrl($retour);
            return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            /** @var Etat $etat */
            $etat = $this->getEntityManager()->getRepository(Etat::class)->find($data["etat"]);

            if ($etat !== null) {
                $session->setEtat($etat);
                $this->getSessionService()->update($session);
                //todo ceci est un test ...
                if ($session->getEtat()->getCode() === Etat::CODE_FERME) {
                    $this->getNotificationService()->triggerSessionImminente($session);
                    $this->getNotificationService()->triggerInscriptionEchec($session);
                }
                if ($session->getEtat()->getCode() === Etat::CODE_CLOTURER) {
                    $this->getNotificationService()->triggerSessionTerminee($session);
                }
            }
        }

        return new ViewModel([
           "title" => "Changement de l'état de la session",
           "etats" => $this->getEntityManager()->getRepository(Etat::class)->findAll(),
           "session" => $session,
        ]);
    }

    public function classerInscriptionsAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $inscriptions = $session->getInscriptions();
        $classements = [Inscription::LISTE_PRINCIPALE => [],  Inscription::LISTE_COMPLEMENTAIRE => [], 'N' => []];
        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            if ($inscription->estNonHistorise()) {
                $liste = $inscription->getListe() ?: 'N';
                $classements[$liste][] = $inscription;
            }
        }
        $positionPrincipale = count($classements[Inscription::LISTE_PRINCIPALE]);
        $positionComplementaire = count($classements[Inscription::LISTE_COMPLEMENTAIRE]);
        usort($classements['N'], function(Inscription $a,Inscription $b) { return $a->getHistoCreation() > $b->getHistoCreation(); });
        foreach ($classements['N'] as $inscription) {

            if ($positionPrincipale < $session->getTailleListePrincipale()) {
                $inscription->setListe(Inscription::LISTE_PRINCIPALE);
                $this->getInscriptionService()->update($inscription);
                $this->getNotificationService()->triggerInscriptionListePrincipale($inscription);
                $positionPrincipale++;
            } else {
                if ($positionComplementaire < $session->getTailleListeComplementaire()) {
                    $inscription->setListe(Inscription::LISTE_COMPLEMENTAIRE);
                    $this->getInscriptionService()->update($inscription);
                    $this->getNotificationService()->triggerInscriptionListeComplementaire($inscription);
                    $positionComplementaire++;
                }
                else {
                    break;
                }
            }
        }

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }

    public function declasserInscriptionsAction() : Response
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        /** @var Inscription $inscription */
        foreach ($session->getInscriptions() as $inscription) {
            $inscription->setListe(null);
            $this->getInscriptionService()->update($inscription);
        }

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }

    public function genererExportAction() : CsvModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $headers = ['Liste', 'Dénomination étudiant', 'Adresse électronique', 'Établissement', 'École doctorale', 'Unité de recherche'];

        $inscriptions = $session->getInscriptions()->toArray();
        $records = [];
        /** @var Inscription $inscription */
        foreach ($inscriptions as $inscription) {
            $doctorant = $inscription->getDoctorant();
            $theses = array_filter($doctorant->getTheses(), function (These $t) { return $t->getEtatThese() === These::ETAT_EN_COURS; });
            $etablissements = array_map(function (These $t) { return ($t->getEtablissement())?$t->getEtablissement()->getLibelle():"Établissement non renseigné";}, $theses);
            $ecoles = array_map(function (These $t) { return ($t->getEcoleDoctorale())?$t->getEcoleDoctorale()->getLibelle():"École doctorale non renseignée";}, $theses);
            $unites = array_map(function (These $t) { return ($t->getUniteRecherche())?$t->getUniteRecherche()->getLibelle():"Unité de recherche non renseignée";}, $theses);
            $entry = [
                'Liste' => $inscription->getListe(),
                'Dénomination étudiant' => $doctorant->getIndividu()->getNomComplet(),
                'Adresse électronique' => $doctorant->getIndividu()->getEmail(),
                'Établissement' => implode("/",$etablissements),
                'École doctorale' => implode("/",$ecoles),
                'Unité de recherche' => implode("/",$unites),
            ];
            $records[] = $entry;
        }

        $filename = (new DateTime())->format('Ymd-His') . '-session-' . str_replace(' ','_',$session->getFormation()->getLibelle()) . '-'. $session->getDateDebut()->format('d_m_Y') .'.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);
        return $CSV;
    }

    public function genererEmargementsAction()
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $seances = $session->getSeances()->toArray();
        $seances = array_filter($seances, function ($a) { return $a->estNonHistorise();});
        usort($seances, function (Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut();});

        $logos = [];
        $logos['site'] = $this->fileService->computeLogoFilePathForStructure($session->getSite());
        if ($comue = $this->etablissementService->fetchEtablissementComue()) {
            $logos['comue'] = $this->fileService->computeLogoFilePathForStructure($comue);
        }

        //exporter
        $export = new EmargementExporter($this->renderer, 'A4');
        $export->setVars([
            'seance' => $seances[0],
            'logos' => $logos,
        ]);
        $export->exportAll($seances, 'SYGAL_emargement_' . $session->getId() . ".pdf");
    }
}