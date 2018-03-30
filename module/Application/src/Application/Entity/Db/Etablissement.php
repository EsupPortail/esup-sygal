<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenApp\Util;

/**
 * Etablissement
 */
class Etablissement implements HistoriqueAwareInterface, SourceAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    const ETAB_PREFIX_SEP = '::';

    const CODE_COMUE = 'COMUE';

    protected $id;
    protected $code;
    protected $domaine;
    protected $theses;
    protected $doctorants;
    protected $roles;

    /**
     * @var Structure
     */
    protected $structure;

    /**
     * Ajoute le préfixe établissement à la chaîne de caractères spécifiée.
     *
     * @param string $string
     * @return string
     */
    public function prependPrefixTo($string)
    {
        return $this->getCode() . self::ETAB_PREFIX_SEP . $string;
    }

    /**
     * Supprime le préfixe établissement à la chaîne de caractères spécifiée.
     *
     * @param string $string
     * @return string
     */
    public function removePrefixFrom($string)
    {
        return substr(
            $string,
            stripos($string, self::ETAB_PREFIX_SEP) + strlen(self::ETAB_PREFIX_SEP)
        );
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
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
        return file_get_contents(APPLICATION_DIR . $this->getCheminLogo()) ?: null;
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
     * @param mixed $theses
     */
    public function setTheses($theses)
    {
        $this->theses = $theses;
    }

    /**
     * @return mixed
     */
    public function getDoctorants()
    {
        return $this->doctorants;
    }

    /**
     * @param mixed $doctorants
     */
    public function setDoctorants($doctorants)
    {
        $this->doctorants = $doctorants;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

}