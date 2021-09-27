<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Formation\Entity\Db\EnqueteReponse;
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
            /** @var EnqueteReponse[] $reponses */
            $reponses = $this->getEntityManager()->getRepository(EnqueteReponse::class)->findEnqueteReponseByDoctorant($doctorant);
            $resultats = [];
            foreach ($reponses as $reponse) {
                if ($reponse->estNonHistorise()) $resultats[$reponse->getInscription()->getId()][] = $reponse;
            }
        } else {
            $ouvertes = [];
            $preparations = [];
            $inscriptions = [];
            $resultats = [];
        }

        return new ViewModel([
            'doctorant' => $doctorant,
            'ouvertes' => $ouvertes,
            'preparations' => $preparations,
            'inscriptions' => $inscriptions,
            'resultats' => $resultats,
        ]);
    }

    public function indexFormateurAction()
    {
        /** @var Individu $individu */
        $individuId = $this->params()->fromRoute('formateur');
        if ($individuId !== null) {
            $individu = $this->getEntityManager()->getRepository(Individu::class)->find($individuId);
        } else {
            $individu = null;
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