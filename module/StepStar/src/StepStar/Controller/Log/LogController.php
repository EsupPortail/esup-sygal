<?php

namespace StepStar\Controller\Log;

use Application\Controller\AbstractController;
use Fichier\FileUtils;
use StepStar\Service\Log\LogServiceAwareTrait;
use Webmozart\Assert\Assert;

class LogController extends AbstractController
{
    use LogServiceAwareTrait;

    public function consulterAction(): array
    {
        /** @var \StepStar\Entity\Db\Log $log */
        $log = $this->logService->getRepository()->find($this->params('log'));

        return [
            'log' => $log,
        ];
    }

    public function telechargerTefAction()
    {
        /** @var \StepStar\Entity\Db\Log $log */
        $log = $this->logService->getRepository()->find($this->params('log'));
        $hash = $this->params('hash');

        Assert::notNull($log, "Log introuvable avec l'id spécifié");
        Assert::notNull($content = $log->getTefFileContent(),
            "Le Log spécifié ne dispose pas du contenu du fichier TEF envoyé");
        Assert::eq($hash, $log->getTefFileContentHash(), "Le hash spécifié est invalide");

        $fileName = sprintf('tef_%s.xml', $log->getTheseId());

        FileUtils::downloadFileFromContent($content, $fileName, 'application/xml');
    }
}