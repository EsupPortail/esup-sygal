<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\Presence;
use Formation\Entity\Db\Seance;
use Formation\Service\Inscription\InscriptionServiceAwareTrait;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use Formation\Service\Seance\SeanceServiceAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class PresenceController extends AbstractController {
    use EntityManagerAwareTrait;
    use InscriptionServiceAwareTrait;
    use PresenceServiceAwareTrait;
    use SeanceServiceAwareTrait;
    use SessionServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        $presences = $this->getPresenceService()->getRepository()->findAll();

        return new ViewModel([
            'presences' => $presences,
        ]);
    }

    public function renseignerPresencesAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);
        $presences = $this->getPresenceService()->getRepository()->findPresencesBySession($session);

        $dictionnaire = [];
        foreach ($presences as $presence) {
            $dictionnaire[$presence->getSeance()->getId()][$presence->getInscription()->getId()] = $presence;
        }

        return new ViewModel([
            'session' => $session,
            'presences' => $dictionnaire,
        ]);
    }

    public function togglePresenceAction() : ViewModel
    {
        $seance = $this->getSeanceService()->getRepository()->getRequestedSeance($this);
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);

        /** @var  Presence $presence */
        $presence = $this->getPresenceService()->getRepository()->findPresenceByInscriptionAndSeance($inscription,$seance);
        if ($presence === null) {
            $presence = new Presence();
            $presence->setInscription($inscription);
            $presence->setSeance($seance);
            $presence->setTemoin('O');
            $this->getPresenceService()->create($presence);
        } else {
            $presence->setTemoin(($presence->getTemoin() === 'O')?'N':'O');
            $this->getPresenceService()->update($presence);
        }

        $vm = new ViewModel();
        $vm->setTemplate('formation/default/reponse');
        $vm->setVariables([
            'reponse' => ($presence->getTemoin() === 'O'),
        ]);
        return $vm;
    }

    public function togglePresencesAction() : ViewModel
    {
        $mode = $this->params()->fromRoute('mode');
        $inscription = $this->getInscriptionService()->getRepository()->getRequestedInscription($this);

        $session = $inscription->getSession();
        $seances = $session->getSeances()->toArray();
        $seances = array_filter($seances, function (Seance $a) { return $a->estNonHistorise();});

        /** @var  Presence $presence */
        foreach ($seances as $seance) {
            $presence = $this->getPresenceService()->getRepository()->findPresenceByInscriptionAndSeance($inscription, $seance);
            if ($presence === null) {
                $presence = new Presence();
                $presence->setSeance($seance);
                $presence->setInscription($inscription);
                $presence->setTemoin(($mode === 'on')?'O':'N');
                $this->getPresenceService()->create($presence);
            } else {
                $presence->setTemoin(($mode === 'on')?'O':'N');
                $this->getPresenceService()->update($presence);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('formation/default/reponse');
        $vm->setVariables([
            'reponse' => ($mode === 'on'),
        ]);
        return $vm;
    }

}