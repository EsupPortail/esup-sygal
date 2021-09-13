<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Doctorant;
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
            $ouvertes = array_filter($sessions, function(Session $a) { return $a->getEtat()->getCode() === Session::ETAT_INSCRIPTION;});
            $preparations = array_filter($sessions, function(Session $a) { return $a->getEtat()->getCode() === Session::ETAT_PREPARATION;});
            /** @var Inscription[] $inscription */
            $inscriptions = $this->getEntityManager()->getRepository(Inscription::class)->findInscriptionsByDoctorant($doctorant);
        } else {
            $ouvertes = [];
            $preparations = [];
            $inscriptions = [];
        }

        return new ViewModel([
            'doctorant' => $doctorant,
            'ouvertes' => $ouvertes,
            'preparations' => $preparations,
            'inscriptions' => $inscriptions,
        ]);
    }

}