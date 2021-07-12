<?php

namespace Application\Controller;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Mvc\Controller\AbstractConsoleController;

class TheseConsoleController extends AbstractConsoleController
{
    use TheseServiceAwareTrait;
    use LoggerAwareTrait;

    /**
     * CLI action.
     *
     * Transfère toutes les données saisies sur une thèse *historisée* vers une autre thèse.
     */
    public function transferTheseDataAction()
    {
        $fromId = $this->params('source-id');
        $toId = $this->params('destination-id');

        /** @var These $fromThese */
        $fromThese = $this->theseService->getRepository()->find($fromId);
        /** @var These $toThese */
        $toThese = $this->theseService->getRepository()->find($toId);

        if ($fromThese === null) {
            throw new RuntimeException("Aucune thèse source trouvée avec cet id : " . $fromId);
        }
        if ($toThese === null) {
            throw new RuntimeException("Aucune thèse destination trouvée avec cet id : " . $toId);
        }

        $this->logger->info("# Transfert de toutes les données saisies sur une thèse *historisée* vers une autre thèse...");

        $this->theseService->transferTheseData($fromThese, $toThese);

        $this->logger->info("Terminé.");

        exit(1);
    }
}