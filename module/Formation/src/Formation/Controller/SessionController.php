<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Module;
use Formation\Entity\Db\Session;
use Formation\Form\Session\SessionFormAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class SessionController extends AbstractController
{
    use EntityManagerAwareTrait;
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

        return new ViewModel([
            'session' => $session,
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

}