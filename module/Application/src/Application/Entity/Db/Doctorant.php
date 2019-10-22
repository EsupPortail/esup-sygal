<?php

namespace Application\Entity\Db;

use Application\Entity\Db\Interfaces\DoctorantInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LogicException;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;
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
    protected $sourceCode;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * @var Collection
     */
    private $complements;

    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * @var string
     */
    private $ine;

    /**
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->complements = new ArrayCollection();
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get numeroEtudiant
     *
     * @return string
     */
    public function getNumeroEtudiant()
    {
        return $this->getIndividu()->getSupannId(); // todo: à remplacer par $this->>numeroEtudiant lorsqu'il sera importé.
    }

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->getIndividu()->setDateNaissance($dateNaissance);

        return $this;
    }

    /**
     * Get dateNaissance
     *
     * @return \DateTime
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getDateNaissance()
    {
        return $this->getIndividu()->getDateNaissance();
    }

    /**
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNationalite()
    {
        return $this->getIndividu()->getNationalite();
    }

    /**
     * @param string $nationalite
     * @return Doctorant
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setNationalite($nationalite)
    {
        $this->getIndividu()->setNationalite($nationalite);

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setEmail($email)
    {
        $this->getIndividu()->setEmail($email);

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getEmail()
    {
        return $this->getIndividu()->getEmail();
    }

    /**
     * Set nomPatronymique
     *
     * @param string $nomPatronymique
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setNomPatronymique($nomPatronymique)
    {
        $this->getIndividu()->setNomPatronymique($nomPatronymique);

        return $this;
    }

    /**
     * Get nomPatronymique
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNomPatronymique()
    {
        return $this->getIndividu()->getNomPatronymique();
    }

    /**
     * Set nomUsuel
     *
     * @param string $nomUsuel
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setNomUsuel($nomUsuel)
    {
        $this->getIndividu()->setNomUsuel($nomUsuel);

        return $this;
    }

    /**
     * Get nomUsuel
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNomUsuel()
    {
        return $this->getIndividu()->getNomUsuel();
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setPrenom($prenom)
    {
        $this->getIndividu()->setPrenom($prenom);

        return $this;
    }

    /**
     * Get prenom
     *
     * @param bool $tous
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getPrenom($tous = false)
    {
        return $tous ? $this->getIndividu()->getPrenoms() : $this->getIndividu()->getPrenom();
    }

    /**
     * Get prenoms
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getPrenoms()
    {
        return $this->getIndividu()->getPrenoms();
    }

    /**
     * Set civilite
     *
     * @param string $civilite
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setCivilite($civilite)
    {
        $this->getIndividu()->setCivilite($civilite);

        return $this;
    }

    /**
     * Get civilite
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getCivilite()
    {
        return $this->getIndividu()->getCivilite();
    }

    /**
     * Get civilite
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getCiviliteToString()
    {
        return $this->getIndividu()->getCiviliteToString();
    }

    /**
     * Get estUneFemme
     *
     * @return bool
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function estUneFemme()
    {
        return $this->getIndividu()->estUneFemme();
    }

    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getIndividu()->__toString();
    }

    /**
     * Get nomUsuel
     *
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenoms
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNomComplet($avecCivilite = false, $avecNomPatro = false, $prenoms = false)
    {
        return $this->getIndividu()->getNomComplet($avecCivilite, $avecNomPatro, $prenoms);
    }

    /**
     * Get dateNaissance
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getDateNaissanceToString()
    {
        return $this->getIndividu()->getDateNaissanceToString();
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return Doctorant
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;

        return $this;
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
        if (!($complement = $this->getComplement())) {
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
        if (!($complement = $this->getComplement())) {
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

    /**
     * @return string
     */
    public function getIne()
    {
        return $this->ine;
    }

    /**
     * @param string $ine
     * @return Individu
     */
    public function setIne($ine)
    {
        $this->ine = $ine;
        return $this;
    }

}
