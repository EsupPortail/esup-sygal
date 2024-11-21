<?php

namespace Application\Entity\Db;

use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

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
     * Adresse complète de la liste de diffusion, ex :
     * - ed591.doctorants.insa@normandie-univ.fr
     * - ed591.doctorants@normandie-univ.fr
     * - ed591.dirtheses@normandie-univ.fr
     *
     * Où :
     * - '591' est le numéro national de l'école doctorale ;
     * - 'doctorants' (ou 'dirtheses') est la "cible" ;
     * - 'insa' est le source_code unique de l'établissement en minuscules.
     *
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
