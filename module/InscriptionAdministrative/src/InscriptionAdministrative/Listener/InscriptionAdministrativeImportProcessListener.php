<?php

namespace InscriptionAdministrative\Listener;

use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcessAwareTrait;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use RuntimeException;
use SygalApiImpl\Event\InscriptionAdministrativeImportEvent;

class InscriptionAdministrativeImportProcessListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    use InscriptionAdministrativeServiceAwareTrait;
    use InscriptionAdministrativeReceptionProcessAwareTrait;

    protected bool $runReceptionProcessAfterImportProcess = false;

    /**
     * Active ou non l'automaticité du traitement de l'IA (par le service {@see InscriptionAdministrativeReceptionProcess})
     * lorsque l'événement {@see InscriptionAdministrativeImportEvent} est reçu.
     */
    public function setRunReceptionProcessAfterImportProcess(bool $runReceptionProcessAfterImportProcess): void
    {
        $this->runReceptionProcessAfterImportProcess = $runReceptionProcessAfterImportProcess;
    }

    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $events->attach(
            InscriptionAdministrativeImportEvent::NAME,
            [$this, 'onImportInscriptionAdministrative'],
            $priority
        );
    }

    public function onImportInscriptionAdministrative(InscriptionAdministrativeImportEvent $event): void
    {
        /** @var \InscriptionAdministrative\Entity\Db\InscriptionAdministrative $ia */
        $iaSourceCode = $event->getInscriptionAdministrativeSourceCode();
        $ia = $this->inscriptionAdministrativeService->getRepository()->findOneBySourceCode($iaSourceCode);
        if ($ia === null) {
            throw new RuntimeException("Impossible de trouver l'IA ayant le source code " . $iaSourceCode);
        }

        if ($this->runReceptionProcessAfterImportProcess) {
            $this->inscriptionAdministrativeReceptionProcess->setInscriptionAdministrative($ia);
            $this->inscriptionAdministrativeReceptionProcess->run();
        }
    }
}