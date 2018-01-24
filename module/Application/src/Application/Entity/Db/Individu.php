<?php

namespace Application\Entity\Db;

use Application\Filter\NomCompletFormatter;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Individu
 */
class Individu implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var string
     */
    private $nomUsuel;

    /**
     * @var string
     */
    private $prenom;

    /**
     * @var string
     */
    private $civilite;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $tel;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var integer
     */
    private $id;

    /**
     * @return string
     */
    function __toString()
    {
        $f = new NomCompletFormatter(true, true);

        return $f->filter($this);
    }

    /**
     * Set nomUsuel
     *
     * @param string $nomUsuel
     *
     * @return Individu
     */
    public function setNomUsuel($nomUsuel)
    {
        $this->nomUsuel = $nomUsuel;

        return $this;
    }

    /**
     * Get nomUsuel
     *
     * @return string
     */
    public function getNomUsuel()
    {
        return $this->nomUsuel;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Individu
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set civilite
     *
     * @param string $civilite
     *
     * @return self
     */
    public function setCivilite($civilite)
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * Get civilite
     *
     * @return string
     */
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * Get civilite
     *
     * @return string
     */
    public function getCiviliteToString()
    {
        return $this->getCivilite();
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Individu
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set tel
     *
     * @param string $tel
     *
     * @return Individu
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Get tel
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
     *
     * @return Individu
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode
     *
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
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

    public function getNomCivil($civilite=true) {
        $text = "";
        if ($civilite) $text .= $this->getCivilite()." ";
        $text .= ucwords(strtolower($this->getPrenom())," -")." ";
        $text .= strtoupper($this->getNomUsuel());
        return $text;
    }
}