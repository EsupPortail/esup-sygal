<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Doctorant\Entity\Db\Doctorant;
use Individu\Entity\Db\Individu;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractController
{
    use EntityManagerAwareTrait;

    public function indexAction() : ViewModel
    {
        return new ViewModel();
    }

    public function indexDoctorantAction() : ViewModel
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

    public function indexFormateurAction() : ViewModel
    {
        /** @var Individu $individu */
        $individuId = $this->params()->fromRoute('formateur');
        if ($individuId !== null) {
            $individu = $this->getEntityManager()->getRepository(Individu::class)->find($individuId);
        } else {
            $user = $this->userContextService->getIdentityDb();
            $individu = ($user)?$user->getIndividu():null;
        }

        if($individu) {
            $passees    = $this->getEntityManager()->getRepository(Session::class)->findSessionsPasseesByFormateur($individu);
            $courantes  = $this->getEntityManager()->getRepository(Session::class)->findSessionsCourantesByFormateur($individu);
            $futures    = $this->getEntityManager()->getRepository(Session::class)->findSessionsFuturesByFormateur($individu);
        } else {
            $passees = [];
            $courantes = [];
            $futures = [];
        }

        return new ViewModel([
            'individu' => $individu,
            'passees' => $passees,
            'courantes' => $courantes,
            'futures' => $futures,
        ]);
    }

}