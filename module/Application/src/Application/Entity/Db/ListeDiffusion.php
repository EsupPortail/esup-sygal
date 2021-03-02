<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * Classe représentant un fichier générique.
 */
class ListeDiffusion implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $adresse;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * Représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getAdresse();
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @param string $adresse
     * @return ListeDiffusion
     */
    public function setAdresse(string $adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return ListeDiffusion
     */
    public function setEnabled(bool $enabled): ListeDiffusion
    {
        $this->enabled = $enabled;
        return $this;
    }
}
