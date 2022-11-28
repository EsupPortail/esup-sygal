<?php

namespace StepStar\Controller\Oai;

use Unicaen\Console\Controller\AbstractConsoleController;
use StepStar\Service\Oai\OaiServiceAwareTrait;

class OaiConsoleController extends AbstractConsoleController
{
    use OaiServiceAwareTrait;

    public function generateConfigFileFromSiseOaiSetXmlFileAction(): void
    {
        $outputDir = $this->params('output-dir') ?: sys_get_temp_dir();

        $filePath = $this->oaiSetService->generateConfigFileFromSiseOaiSetXmlFile($outputDir);

        $this->console->writeLine(sprintf(
            "> Le fichier de config '%s' a ete genere a partir du fichier XML '%s'.",
            realpath($filePath), realpath($this->oaiSetService->getSiseOaiSetXmlFilePath())
        ));
    }

    public function generateConfigFileFromOaiSetsXmlFileAction()
    {
        $outputDir = $this->params('output-dir') ?: sys_get_temp_dir();

        $filePath = $this->oaiSetService->generateConfigFileFromOaiSetsXmlFile($outputDir);

        $this->console->writeLine(sprintf(
            "> Le fichier de config '%s' a ete genere a partir du fichier XML '%s'.",
            realpath($filePath), realpath($this->oaiSetService->getOaiSetsXmlFilePath())
        ));
    }

}