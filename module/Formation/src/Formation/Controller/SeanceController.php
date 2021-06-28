<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use DateTime;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Formation\Service\Seance\SeanceServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class SeanceController extends AbstractController
{
    use EntityManagerAwareTrait;
    use SeanceServiceAwareTrait;

    public function indexAction()
    {
        /** @var Seance[] $seances */
        $seances = $this->getEntityManager()->getRepository(Seance::class)->findAll();

        return new ViewModel([
            'seances' => $seances,
        ]);
    }

    public function ajouterAction()
    {
        $seance = new Seance();
        /** @var Session $bidon */
        $bidon = $this->getEntityManager()->getRepository(Session::class)->find(2);
        $seance->setSession($bidon);
        $today = (new DateTime())->format('d/m/Y');
        $seance->setDebut(DateTime::createFromFormat('d/m/Y H:i', ($today . " 08:00")));
        $seance->setFin(DateTime::createFromFormat('d/m/Y H:i', ($today . " 12:00")));
        $seance->setLieu("Ici ou labas");
        $this->getSeanceService()->create($seance);

        return $this->redirect()->toRoute('formation/seance');
    }
}