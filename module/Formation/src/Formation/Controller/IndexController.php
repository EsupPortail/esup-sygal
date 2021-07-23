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
        $doctorantId = $this->params()->fromRoute('doctorant');
        if ($doctorantId !== null) {
            $doctorant = $this->getEntityManager()->getRepository(Doctorant::class)->find($doctorantId);
        } else {
            $doctorant = null;
        }

        if($doctorant) {
            /** @var Session[] $session */
            $sessions = $this->getEntityManager()->getRepository(Session::class)->findSessionsDisponiblesByDoctorant($doctorant);
            /** @var Inscription[] $inscription */
            $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);
        } else {
            $sessions = [];
            $inscriptions = [];
        }

        return new ViewModel([
            'doctorant' => $doctorant,
            'sessions' => $sessions,
            'inscriptions' => $inscriptions,
        ]);
    }

}