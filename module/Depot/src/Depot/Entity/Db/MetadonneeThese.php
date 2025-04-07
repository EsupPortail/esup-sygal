<?php

namespace Depot\Entity\Db;

use These\Entity\Db\These;

/**
 * MetadonneeThese
 */
class MetadonneeThese
{
    /**
     * Codes ISO 639-1 des langues.
     */
    public const LANGUE_FRANCAIS = 'fr';
    public const LANGUE_ANGLAIS  = 'en';
    public const LANGUE_ESPAGNOL = 'es';
    public const LANGUE_ALLEMAND = 'de';
    public const LANGUE_ITALIEN  = 'it';
    public const LANGUE_AUTRE    = 'AUTRE';

    static public $langues = [
        self::LANGUE_FRANCAIS => "Français",
        self::LANGUE_ANGLAIS  => "Anglais ",
        self::LANGUE_ESPAGNOL => "Espagnol",
        self::LANGUE_ALLEMAND => "Allemand",
        self::LANGUE_ITALIEN  => "Italien",
        self::LANGUE_AUTRE    => "Autre",
    ];

    public const SEPARATEUR_MOTS_CLES = '*';
    public const SEPARATEUR_MOTS_CLES_LIB = 'astérisque';

    /**
     * @var string
     */
    private $langue;

    /**
     * @var string
     */
    private $motsClesLibresFrancais;

    /**
     * @var string
     */
    private $motsClesLibresAnglais;

    /**
     * @var string
     */
    private $resume;

    /**
     * @var string
     */
    private $resumeAnglais;

    /**
     * @var string
     */
    private $titre;

    /**
     * @var string
     */
    private $titreAutreLangue;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var These
     */
    private $these;

    /**
     * Set langue
     *
     * @param string $langue
     *
     * @return MetadonneeThese
     */
    public function setLangue($langue)
    {
        $this->langue = $langue;

        return $this;
    }

    /**
     * Get langue
     *
     * @return string
     */
    public function getLangue()
    {
        return $this->langue;
    }

    /**
     * Set motsClesLibres
     *
     * @param string $motsClesLibresFrancais
     *
     * @return MetadonneeThese
     */
    public function setMotsClesLibresFrancais($motsClesLibresFrancais)
    {
        $this->motsClesLibresFrancais = $motsClesLibresFrancais;

        return $this;
    }

    /**
     * Get motsClesLibres
     *
     * @return string
     */
    public function getMotsClesLibresFrancais()
    {
        return $this->motsClesLibresFrancais;
    }

    /**
     * @return string
     */
    public function getMotsClesLibresAnglais()
    {
        return $this->motsClesLibresAnglais;
    }

    /**
     * @param string $motsClesLibresAnglais
     * @return MetadonneeThese
     */
    public function setMotsClesLibresAnglais($motsClesLibresAnglais)
    {
        $this->motsClesLibresAnglais = $motsClesLibresAnglais;

        return $this;
    }

    /**
     * Set resume
     *
     * @param string $resume
     *
     * @return MetadonneeThese
     */
    public function setResume($resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * Get resume
     *
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set resumeAnglais
     *
     * @param string $resumeAnglais
     *
     * @return MetadonneeThese
     */
    public function setResumeAnglais($resumeAnglais)
    {
        $this->resumeAnglais = $resumeAnglais;

        return $this;
    }

    /**
     * Get resumeAnglais
     *
     * @return string
     */
    public function getResumeAnglais()
    {
        return $this->resumeAnglais;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return MetadonneeThese
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set titreAutreLangue
     *
     * @param string $titreAutreLangue
     *
     * @return MetadonneeThese
     */
    public function setTitreAutreLangue($titreAutreLangue)
    {
        $this->titreAutreLangue = $titreAutreLangue;

        return $this;
    }

    /**
     * Get titreAutreLangue
     *
     * @return string
     */
    public function getTitreAutreLangue()
    {
        return $this->titreAutreLangue;
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
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return self
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }
}

