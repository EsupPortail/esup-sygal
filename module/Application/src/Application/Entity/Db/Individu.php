<?php

namespace Application\Entity\Db;

use Application\Constants;
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
    protected $civilite;

    /**
     * @var string
     */
    protected $nationalite;

    /**
     * @var \DateTime
     */
    protected $dateNaissance;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $nomPatronymique;

    /**
     * @var string
     */
    protected $nomUsuel;

    /**
     * @var string
     */
    protected $prenom1;

    /**
     * @var string
     */
    protected $prenom2;

    /**
     * @var string
     */
    protected $prenom3;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var integer
     */
    private $id;

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return self
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance
     *
     * @return \DateTime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @return string
     */
    public function getNationalite()
    {
        return $this->nationalite;
    }

    /**
     * @param string $nationalite
     * @return Individu
     */
    public function setNationalite($nationalite)
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
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
     * Set nomPatronymique
     *
     * @param string $nomPatronymique
     *
     * @return self
     */
    public function setNomPatronymique($nomPatronymique)
    {
        $this->nomPatronymique = $nomPatronymique;

        return $this;
    }

    /**
     * Get nomPatronymique
     *
     * @return string
     */
    public function getNomPatronymique()
    {
        return $this->nomPatronymique;
    }

    /**
     * Set nomUsuel
     *
     * @param string $nomUsuel
     *
     * @return self
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
     * @return string
     */
    public function getPrenom1()
    {
        return $this->prenom1;
    }

    /**
     * @param string $prenom1
     * @return Individu
     */
    public function setPrenom1($prenom1)
    {
        $this->prenom1 = $prenom1;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom2()
    {
        return $this->prenom2;
    }

    /**
     * @param string $prenom2
     * @return Individu
     */
    public function setPrenom2($prenom2)
    {
        $this->prenom2 = $prenom2;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom3()
    {
        return $this->prenom3;
    }

    /**
     * @param string $prenom3
     * @return Individu
     */
    public function setPrenom3($prenom3)
    {
        $this->prenom3 = $prenom3;

        return $this;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return self
     */
    public function setPrenom($prenom)
    {
        return $this->setPrenom1($prenom);
    }

    /**
     * Get prenom
     *
     * @param bool $tous
     * @return string
     */
    public function getPrenom($tous = false)
    {
        return $tous ? $this->getPrenoms() : $this->getPrenom1();
    }

    /**
     * Get prenoms
     *
     * @return string
     */
    public function getPrenoms()
    {
        return join(' ', array_filter([
            $this->getPrenom1(),
            $this->getPrenom2(),
            $this->getPrenom3(),
        ]));
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
     * Get estUneFemme
     *
     * @return bool
     */
    public function estUneFemme()
    {
        return 'Mme' === $this->getCivilite();
    }

    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        $f = new NomCompletFormatter(true, true);

        return $f->filter($this);
    }

    /**
     * Get nomUsuel
     *
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenoms
     * @return string
     */
    public function getNomComplet($avecCivilite = false, $avecNomPatro = false, $prenoms = false)
    {
        $f = new NomCompletFormatter(true, $avecCivilite, $avecNomPatro, false, $prenoms);

        return $f->filter($this);
    }

    /**
     * Get dateNaissance
     *
     * @return string
     */
    public function getDateNaissanceToString()
    {
        return $this->dateNaissance->format(Constants::DATE_FORMAT);
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

    /**
     * @param bool $civilite
     * @return string
     */
    public function getNomCivil($civilite = true)
    {
        $text = "";
        if ($civilite) $text .= $this->getCivilite() . " ";
        $text .= ucwords(strtolower($this->getPrenom()), " -") . " ";
        $text .= strtoupper($this->getNomUsuel());

        return $text;
    }
}