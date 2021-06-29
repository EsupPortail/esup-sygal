<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Module;
use Formation\Entity\Db\Session;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class SessionController extends AbstractController
{
    use EntityManagerAwareTrait;
    use SessionServiceAwareTrait;

    public function indexAction()
    {
        /** @var Session[] $sessions */
        $sessions = $this->getEntityManager()->getRepository(Session::class)->findAll();

        return new ViewModel([
            'sessions' => $sessions,
        ]);
    }

    public function ajouterAction()
    {
        /** @var Module $module */
        $module = $this->getEntityManager()->getRepository(Module::class)->getRequestedModule($this);

        $session = new Session();
        $session->setModule($module);
        $session = $this->getSessionService()->setValeurParDefaut($session);
        $this->getSessionService()->create($session);

        return $this->redirect()->toRoute('formation/session');
    }
}