<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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


}