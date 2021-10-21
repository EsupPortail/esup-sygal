<?php

namespace Application\Command\Pdf;

use Application\Command\MergeShellCommand;

/**
 * Commande d'ajout de la page de couverture au manuscrit de thèse.
 *
 * Possibilité de retirer la 1ere page du manuscrit.
 *
 * Version utilisant 'qpdf'.
 */
class AjoutPdcShellCommandQpdf extends PdfMergeShellCommandQpdf
{
    use AjoutPdcShellCommandTrait;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'AjoutPdcShellCommandQpdf';
    }

    /**
     * Spécifie les 2 fichiers d'entrée : page de couverture et manuscrit.
     *
     * @param array $inputFilesPaths Format attendu : ['couverture' => string, 'manuscrit' => string]
     * @return self
     */
    public function setInputFilesPaths(array $inputFilesPaths): MergeShellCommand
    {
        parent::setInputFilesPaths($inputFilesPaths);
        $this->processInputFilesPaths($inputFilesPaths);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        $manuscritFirstPageRangeSpec = $this->supprimer1erePageDuManuscrit ? 2 : 1;

        // Commande de fusion (cf. https://qpdf.sourceforge.io/files/qpdf-manual.html#ref.page-selection)
        $command = $this->executable .
            sprintf(' %s --pages %s 1-z . %d-z -- %s',
                $this->manuscritInputFilePath,
                $this->couvertureInputFilePath,
                $manuscritFirstPageRangeSpec,
                $this->outputFilePath
            );

        $this->commandLine = $command;
    }
}