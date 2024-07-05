<?php

namespace Depot\Entity\Db;

/**
 * MetadonneeThese
 */
class MetadonneeThese
{
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
     * @var int
     */
    private $id;

    /**
     * @var \These\Entity\Db\These
     */
    private $these;


    /**
     * Set langue.
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
     * Get langue.
     *
     * @return string
     */
    public function getLangue()
    {
        return $this->langue;
    }

    /**
     * Set motsClesLibresFrancais.
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
     * Get motsClesLibresFrancais.
     *
     * @return string
     */
    public function getMotsClesLibresFrancais()
    {
        return $this->motsClesLibresFrancais;
    }

    /**
     * Set motsClesLibresAnglais.
     *
     * @param string $motsClesLibresAnglais
     *
     * @return MetadonneeThese
     */
    public function setMotsClesLibresAnglais($motsClesLibresAnglais)
    {
        $this->motsClesLibresAnglais = $motsClesLibresAnglais;

        return $this;
    }

    /**
     * Get motsClesLibresAnglais.
     *
     * @return string
     */
    public function getMotsClesLibresAnglais()
    {
        return $this->motsClesLibresAnglais;
    }

    /**
     * Set resume.
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
     * Get resume.
     *
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set resumeAnglais.
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
     * Get resumeAnglais.
     *
     * @return string
     */
    public function getResumeAnglais()
    {
        return $this->resumeAnglais;
    }

    /**
     * Set titre.
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
     * Get titre.
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set titreAutreLangue.
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
     * Get titreAutreLangue.
     *
     * @return string
     */
    public function getTitreAutreLangue()
    {
        return $this->titreAutreLangue;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set these.
     *
     * @param \These\Entity\Db\These|null $these
     *
     * @return MetadonneeThese
     */
    public function setThese(\These\Entity\Db\These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these.
     *
     * @return \These\Entity\Db\These|null
     */
    public function getThese()
    {
        return $this->these;
    }
}
