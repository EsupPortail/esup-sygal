<?php

namespace Fichier\Command\Pdf;

use Webmozart\Assert\Assert;

trait AjoutPdcShellCommandTrait
{
    protected $manuscritInputFilePath;
    protected $couvertureInputFilePath;
    protected $supprimer1erePageDuManuscrit = false;

    /**
     * @param bool $supprimer1erePageDuManuscrit
     * @return self
     */
    public function setSupprimer1erePageDuManuscrit(bool $supprimer1erePageDuManuscrit = true): self
    {
        $this->supprimer1erePageDuManuscrit = $supprimer1erePageDuManuscrit;
        return $this;
    }

    /**
     * Recherche dans les 2 fichiers d'entrée la page de couverture et le manuscrit.
     *
     * @param array $inputFilesPaths Format attendu : ['couverture' => string, 'manuscrit' => string]
     */
    public function processInputFilesPaths(array $inputFilesPaths)
    {
        Assert::keyExists($inputFilesPaths, 'couverture');
        Assert::keyExists($inputFilesPaths, 'manuscrit');
        $this->couvertureInputFilePath = $inputFilesPaths['couverture'];
        $this->manuscritInputFilePath = $inputFilesPaths['manuscrit'];
    }
}