<?php

namespace Fichier\Command\Pdf;

use Application\Command\ShellCommand;
use These\Entity\Db\FichierThese;

/**
 * Commande/script de retraitement d'un manuscrit de thèse pour le rendre archivable.
 */
class RetraitementShellCommand extends ShellCommand
{
    protected $executable = APPLICATION_DIR . '/bin/fichier-retraiter.sh';

    /**
     * @var string
     */
    protected $destinataires;

    /**
     * @var \These\Entity\Db\FichierThese
     */
    protected $fichierThese;

    /**
     * @param string $destinataires
     * @return self
     */
    public function setDestinataires(string $destinataires): self
    {
        $this->destinataires = $destinataires;
        return $this;
    }

    /**
     * @param \These\Entity\Db\FichierThese $fichierThese
     * @return self
     */
    public function setFichierThese(FichierThese $fichierThese): self
    {
        $this->fichierThese = $fichierThese;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'RetraitementShellCommand';
    }

    /**
     * @inheritDoc
     */
    public function generateCommandLine()
    {
        $this->commandLine = $this->executable .
            sprintf(' --tester-archivabilite --notifier="%s" %s', $this->destinataires, $this->fichierThese->getId());
    }
}