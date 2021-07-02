<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Doctorant;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractController
{
    use EntityManagerAwareTrait;


    public function indexAction()
    {
        return new ViewModel();
    }

    public function indexDoctorantAction()
    {
        /** @var Doctorant $doctorant */
        $doctorant = $this->getEntityManager()->getRepository(Doctorant::class)->find(38765);

        /** @var Session[] $session */
        $sessions = $this->getEntityManager()->getRepository(Session::class)->findSessionsDisponiblesByDoctorant($doctorant);

        /** @var Inscription[] $inscription */
        $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);

        return new ViewModel([
            'doctorant' => $doctorant,
            'sessions' => $sessions,
            'inscriptions' => $inscriptions,
        ]);
    }

}