<?php

namespace Application\Entity\Db;

use Application\Filter\EtablissementPrefixFilter;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Util;
use UnicaenImport\Entity\Db\Interfaces\SourceAwareInterface;
use Application\Entity\Db\Traits\SourceAwareTrait;

/**
 * Etablissement
 */
class Etablissement implements StructureConcreteInterface, HistoriqueAwareInterface, SourceAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    const CODE_STRUCTURE_COMUE = Structure::CODE_COMUE;

    protected $id;
    protected $domaine;
    protected $theses;
    protected $doctorants;
    protected $roles;

    /**
     * @var Structure
     */
    protected $structure;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var bool
     */
    protected $estMembre = false;

    /**
     * @var bool
     */
    protected $estAssocie = false;


    /**
     * Ajoute le préfixe établissement à la chaîne de caractères spécifiée.
     *
     * @param string $string
     * @return string
     */
    public function prependPrefixTo($string)
    {
        $filter = new EtablissementPrefixFilter();

        return $filter->addPrefixEtablissementTo($string, $this);
    }

    /**
     * Supprime le préfixe établissement à la chaîne de caractères spécifiée.
     *
     * @param string $string
     * @return string
     */
    public function removePrefixFrom($string)
    {
        $filter = new EtablissementPrefixFilter();

        return $filter->removePrefixFrom($string);
    }

    /**
     * Etablissement constructor.
     */
    public function __construct()
    {
        $this->structure = new Structure();
    }

    /**
     * Etablissement prettyPrint
     * @return string
     */
    public function __toString()
    {
        return $this->structure->getLibelle();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     * @see StructureConcreteInterface
     */
    public function getCode()
    {
        return $this->getStructure()->getCode();
    }

    /**
     * @return string
     */
    public function generateUniqCode()
    {
        return uniqid();
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
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
     * @return string
     */
    public function getDomaine()
    {
        return $this->domaine;
    }

    /**
     * @param string $domaine
     */
    public function setDomaine($domaine)
    {
        $this->domaine = $domaine;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->getStructure()->getLibelle();
    }

    /**
     * @param string $libelle
     */
    public function setLibelle($libelle)
    {
        $this->getStructure()->setLibelle($libelle);
    }

    /**
     * @return string
     */
    public function getSigle()
    {
        return $this->getStructure()->getSigle();
    }

    /**
     * @param string $sigle
     */
    public function setSigle($sigle)
    {
        $this->getStructure()->setSigle($sigle);
    }

    /**
     * @return bool
     */
    public function estMembre()
    {
        return $this->estMembre;
    }

    /**
     * @param bool $estMembre
     * @return Etablissement
     */
    public function setEstMembre($estMembre)
    {
        $this->estMembre = $estMembre;

        return $this;
    }

    /**
     * @return bool
     */
    public function estAssocie()
    {
        return $this->estAssocie;
    }

    /**
     * @param bool $estAssocie
     * @return Etablissement
     */
    public function setEstAssocie($estAssocie)
    {
        $this->estAssocie = $estAssocie;

        return $this;
    }

    /**
     * @return string
     */
    public function getCheminLogo()
    {
        return $this->getStructure()->getCheminLogo();
    }

    /**
     * @param string $cheminLogo
     */
    public function setCheminLogo($cheminLogo)
    {
        $this->getStructure()->setCheminLogo($cheminLogo);
    }

    /**
     * @return string
     */
    public function getLogoContent()
    {
        if ($this->getCheminLogo() === null) {
            $image = Util::createImageWithText("Aucun logo pour l'Etab|" . $this->getSigle(), 200, 200);
            return $image;
        }
        if (!file_exists(Structure::PATH . $this->getCheminLogo())) {
            $image = Util::createImageWithText("Fichier absent sur le HD",200,200);
            return $image;
        }
        return file_get_contents( Structure::PATH . $this->getCheminLogo()) ?: null;
    }

    /**
     * @param Structure $structure
     * @return self
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return mixed
     */
    public function getTheses()
    {
        return $this->theses;
    }

    /**
     * @return mixed
     */
    public function getDoctorants()
    {
        return $this->doctorants;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }
}