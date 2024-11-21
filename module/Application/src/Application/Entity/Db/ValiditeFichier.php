<?php

namespace Application\Entity\Db;

use DateTime;
use Fichier\Entity\Db\Fichier;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * Classe d'entité se voulant générique.
 * Le seul usage est pour l'instant : valide = archivable.
 */
class ValiditeFichier implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var bool
     */
    private $estValide;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $log;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, [
            '- Date: ' . $this->getHistoModification()->format('d/m/Y H:i:s'),
            '- Valide: ' . $this->getEstValideToString(),
            '- Message: ' . $this->getMessage(),
            '- Log: ' . $this->getLog(),
        ]);
    }

    /**
     * Get histoModification
     */
    public function getHistoModification(): ?DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    /**
     * Set estValide
     *
     * @param bool|null $estValide
     *
     * @return ValiditeFichier
     */
    public function setEstValide(?bool $estValide = true): self
    {
        $this->estValide = $estValide;

        return $this;
    }

    /**
     * Get estValide
     *
     * @return bool|null
     * <code>true</code>  : valide (ex : archivable) ;
     * <code>false</code> : non valide (ex : non archivable) ;
     * <code>null</code>  : validité indéterminée (ex : archivabilité indéterminée car plantage lors du test).
     */
    public function getEstValide(): ?bool
    {
        return $this->estValide;
    }

    public function getEstValideToString(): string
    {
        if ($this->estValide === null) {
            return 'Indéterminé';
        }

        return $this->estValide ? 'Oui' : 'Non';
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return ValiditeFichier
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     * @return ValiditeFichier
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Set fichier
     *
     * @param Fichier $fichier
     *
     * @return ValiditeFichier
     */
    public function setFichier(Fichier $fichier = null)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return Fichier
     */
    public function getFichier()
    {
        return $this->fichier;
    }
}

