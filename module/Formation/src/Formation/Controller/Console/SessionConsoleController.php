<?php

namespace Formation\Controller\Console;

use Formation\Service\Session\SessionServiceAwareTrait;
use Laminas\Mvc\Console\Controller\AbstractConsoleController;
use UnicaenApp\Exception\RuntimeException;

class SessionConsoleController extends AbstractConsoleController
{
    use SessionServiceAwareTrait;

    public function terminerAutoAction()
    {
        $this->console->writeLine("# Terminaison des sessions dont toutes les séances sont passées...");
        try {
            $ids = $this->sessionService->terminerSessionsDontToutesSeancesPassees();
            $this->console->writeLine(empty($ids) ?
                "Aucun session à mettre à jour." :
                "Sessions mises à jour : " . implode(', ', $ids) . '.'
            );
        } catch (\Exception $e) {
            throw new RuntimeException("Une erreur est survenue pendant l'opération.", null, $e);
        }
        $this->console->writeLine("Fait.");
    }
}