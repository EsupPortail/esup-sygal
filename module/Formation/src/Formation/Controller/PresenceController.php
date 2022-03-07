<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Entity\Db\FormationInstancePresence;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Presence;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use Formation\Service\Presence\PresenceServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class PresenceController extends AbstractController {
    use EntityManagerAwareTrait;
    use PresenceServiceAwareTrait;

    public function indexAction()
    {
        /** @var Presence[] $presences */
        $presences = $this->getEntityManager()->getRepository(Presence::class)->findAll();

        return new ViewModel([
            'presences' => $presences,
        ]);
    }

    public function renseignerPresencesAction()
    {
        $session = $this->getEntityManager()->getRepository(Session::class)->getRequestedSession($this);
        $presences = $this->getEntityManager()->getRepository(Presence::class)->findPresencesBySession($session);

        $dictionnaire = [];
        foreach ($presences as $presence) {
            $dictionnaire[$presence->getSeance()->getId()][$presence->getInscription()->getId()] = $presence;
        }

        return new ViewModel([
            'session' => $session,
            'presences' => $dictionnaire,
        ]);
    }

    public function togglePresenceAction()
    {
        $seance = $this->getEntityManager()->getRepository(Seance::class)->getRequestedSeance($this);
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);

        /** @var  Presence $presence */
        $presence = $this->getEntityManager()->getRepository(Presence::class)->findPresenceByInscriptionAndSeance($inscription,$seance);
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

    public function togglePresencesAction()
    {
        $mode = $this->params()->fromRoute('mode');
        /** @var Inscription $inscription */
        $inscription = $this->getEntityManager()->getRepository(Inscription::class)->getRequestedInscription($this);

        $session = $inscription->getSession();
        $seances = $session->getSeances();

        /** @var  Presence $presence */
        foreach ($seances as $seance) {
            $presence = $this->getEntityManager()->getRepository(Presence::class)->findPresenceByInscriptionAndSeance($inscription, $seance);
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