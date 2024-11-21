<?php

namespace Information\Entity\Db;

use DateTime;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * --- Class Information ---
 *
 * @var integer  $id                    un identifiant unique servant de clef
 * @var string   $titre                 Le titre de la page
 * @var string   $contenu               Le contenu de la page
 */
class Information implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    const DEFAULT_PRIORITE = 0;

    /** @var integer */
    private $id;
    /** @var string */
    private $titre;
    /** @var string */
    private $contenu;

    /** @var  integer*/
    private $priorite;
    /** @var boolean */
    private $visible;
    /** @var InformationLangue */
    private $langue;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get histoModification
     */
    public function getHistoModification(): ?DateTime
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    /**
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * @param string $titre
     * @return Information
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * @param string $contenu
     * @return Information
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriorite()
    {
        return $this->priorite;
    }

    /**
     * @param int $priorite
     * @return Information
     */
    public function setPriorite($priorite)
    {
        $this->priorite = $priorite;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return Information
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return InformationLangue
     */
    public function getLangue(): InformationLangue
    {
        return $this->langue;
    }

    /**
     * @param InformationLangue $langue
     */
    public function setLangue(InformationLangue $langue): void
    {
        $this->langue = $langue;
    }



}