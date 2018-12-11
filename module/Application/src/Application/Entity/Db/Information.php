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

    /** @var integer */
    private $id;
    /** @var string */
    private $titre;
    /** @var string */
    private $contenu;

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
}