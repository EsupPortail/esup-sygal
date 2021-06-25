<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Formation;
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
        $session = new Session();
        /** @var Formation $bidon */
        $bidon = $this->getEntityManager()->getRepository(Formation::class)->find(2);
        $session->setFormation($bidon);
        $this->getSessionService()->create($session);

        return $this->redirect()->toRoute('formation/session');
    }
}