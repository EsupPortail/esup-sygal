<?php

namespace StepStar\Controller;

use Application\Controller\AbstractController;
use StepStar\Entity\Db\Log;
use StepStar\Service\Log\LogServiceAwareTrait;

class IndexController extends AbstractController
{
    use LogServiceAwareTrait;

    private array $infos;

    /**
     * @param array $infos
     */
    public function setInfos(array $infos): void
    {
        $this->infos = $infos;
    }

    public function indexAction(): array
    {
        return [];
    }

    public function infosAction(): array
    {
        $lastLog = $this->logService->findLastLogForOperation(Log::OPERATION__ENVOI);

        return [
            'infos' => $this->infos,
            'lastLog' => $lastLog,
        ];
    }
}