<?php

namespace InscriptionAdministrative\Listener;

use InscriptionAdministrative\Process\Reception\ReceptionProcessAwareTrait;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use RuntimeException;
use SygalApiImpl\Event\InscriptionAdministrativeImportEvent;

class ImportProcessListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    use InscriptionAdministrativeServiceAwareTrait;
    use ReceptionProcessAwareTrait;

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

        $this->receptionProcess->setInscriptionAdministrative($ia);
//        $this->receptionProcess->run();
    }
}