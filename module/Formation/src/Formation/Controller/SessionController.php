<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Module;
use Formation\Entity\Db\Presence;
use Formation\Entity\Db\Session;
use Formation\Form\Session\SessionFormAwareTrait;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class SessionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use InscriptionServiceAwareTrait;
    use SessionServiceAwareTrait;

    use SessionFormAwareTrait;

    public function indexAction()
    {
        /** @var Session[] $sessions */
        $sessions = $this->getEntityManager()->getRepository(Session::class)->findAll();

        return new ViewModel([
            'sessions' => $sessions,
        ]);
    }

    public function afficherAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);

        $presences = $this->getEntityManager()->getRepository(Presence::class)->findPresencesBySession($session);
        $dictionnaire = [];
        foreach ($presences as $presence) {
            $dictionnaire[$presence->getSeance()->getId()][$presence->getInscription()->getId()] = $presence;
        }

        return new ViewModel([
            'session' => $session,
            'presences' => $dictionnaire,
        ]);
    }

    public function ajouterAction()
    {
        /** @var Module $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

        $session = new Session();
        $session->setModule($module);
        $session = $this->getSessionService()->setValeurParDefaut($session);

        $form = $this->getSessionForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/ajouter', ['module' => $module->getId()], [], true));
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

    public function modifierAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);

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

    public function historiserAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getSessionService()->historise($session);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session');
    }

    public function restaurerAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);
        $retour = $this->params()->fromQuery('retour');

        $this->getSessionService()->restore($session);

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session');
    }

    public function supprimerAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);

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

    public function classerInscriptionsAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);
        $retour = $this->params()->fromQuery('retour');

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
                $positionPrincipale++;
            } else {
                if ($positionComplementaire < $session->getTailleListeComplementaire()) {
                    $inscription->setListe(Inscription::LISTE_COMPLEMENTAIRE);
                    $this->getInscriptionService()->update($inscription);
                    $positionComplementaire++;
                }
                else {
                    break;
                }
            }
        }

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }

    public function declasserInscriptionsAction()
    {
        /**@var Session $session */
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);
        $retour = $this->params()->fromQuery('retour');

        /** @var Inscription $inscription */
        foreach ($session->getInscriptions() as $inscription) {
            $inscription->setListe(null);
            $this->getInscriptionService()->update($inscription);
        }

        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $session->getId()], [], true);
    }
}