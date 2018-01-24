<?php

namespace Application\Entity\Db;

use Application\Constants;
use Application\Entity\Db\Interfaces\DoctorantInterface;
use Application\Filter\NomCompletFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LogicException;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;
use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Doctorant
 */
class Doctorant implements DoctorantInterface, HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var integer
     */
    protected $id;

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
    protected $prenom;

    /**
     * @var string
     */
    protected $prenoms;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var string
     */
    protected $tel;

    /**
     * @var Collection
     */
    private $complements;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->complements = new ArrayCollection();
    }

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
     * @return Doctorant
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
     * Set prenom
     *
     * @param string $prenom
     *
     * @return self
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @param bool $tous
     * @return string
     */
    public function getPrenom($tous = false)
    {
        return $tous ? $this->prenoms : $this->prenom;
    }

    /**
     * Get prenoms
     *
     * @return string
     */
    public function getPrenoms()
    {
        return $this->prenoms;
    }

    /**
     * Set prenoms
     *
     * @param string $prenoms
     *
     * @return self
     */
    public function setPrenoms($prenoms)
    {
        $this->prenoms = $prenoms;

        return $this;
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
     *
     * @return self
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
     * Set tel
     *
     * @param string $tel
     *
     * @return self
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return DoctorantCompl|null
     */
    public function getComplement()
    {
        return $this->complements->first() ?: null;
    }

    /**
     * @param DoctorantCompl $complement
     * @return static
     * @throws LogicException S'il existe déjà un complément lié
     */
    public function setComplement(DoctorantCompl $complement)
    {
        // NB: le to-many 'complements' est utilisé comme un to-one
        if ($this->complements->count() > 0) {
            throw new LogicException(sprintf("Il existe déjà un enregistrement '%s' lié", get_class($this->getComplement())));
        }

        $this->complements->add($complement);

        return $this;
    }

    /**
     * Convenient method for $this->getComplement()->getPersopass()
     *
     * @return null|string
     */
    public function getPersopass()
    {
        if (! ($complement = $this->getComplement())) {
            return null;
        }

        return $complement->getPersopass();
    }

    /**
     * Convenient method for $this->getComplement()->getEmailPro()
     *
     * @return null|string
     */
    public function getEmailPro()
    {
        if (! ($complement = $this->getComplement())) {
            return null;
        }

        return $complement->getEmailPro();
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     * @see ResourceInterface
     */
    public function getResourceId()
    {
        return 'Doctorant';
    }
}
