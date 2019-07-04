<?php

namespace Application\Entity\Db;

use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Classe représentant un fichier lié *à une thèse*.
 */
class FichierThese implements ResourceInterface
{
    /**
     * Tag marquant un fichier qui résulte d'un deuxième dépôt (après correction).
     *
     * 'V2' = Deuxième dépôt (après corrections)
     */
    const TAG_DEUXIEME_DEPOT = 'V2';

    const RESOURCE_ID = 'FichierThese';

    const MESSAGE_RETRAITEMENT_DUREE = "L'opération de retraitement automatique de fichier peut durer quelques minutes.";
    const MESSAGE_DEPOT_DUREE = "L'opération de téléversement de fichier peut durer quelques minutes.";

    const RETRAITEMENT_AUTO = 'sygal';
    const RETRAITEMENT_MANU = 'Inconnu';

    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $estAnnexe = false;

    /**
     * @var string
     */
    private $retraitement;

    /**
     * @var bool
     */
    private $estExpurge = false;

    /**
     * @var null|int
     */
    private $estConforme = null;

    /**
     * @var bool
     */
    private $estPartiel = false;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * @var These
     */
    private $these;

    /**
     * Représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fichier;
    }

    /**
     * Détermine si ce fichier peut faire l'objet d'un test de validité (i.e. compatibilité STAR).
     *
     * @return bool
     */
    public function supporteTestValidite()
    {
        return ! $this->getEstAnnexe() && ! $this->getEstExpurge();
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     * @deprecated Exploiter la NatureFichier
     */
    public function getEstAnnexe()
    {
        return $this->estAnnexe;
    }

    /**
     * @param boolean $estAnnexe
     * @return self
     * @deprecated Exploiter la NatureFichier
     */
    public function setEstAnnexe($estAnnexe = true)
    {
        $this->estAnnexe = $estAnnexe;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRetraitementAuto()
    {
        return $this->getRetraitement() === self::RETRAITEMENT_AUTO;
    }

    /**
     * @return bool
     */
    public function isRetraitementManuel()
    {
        return $this->getRetraitement() === self::RETRAITEMENT_MANU;
    }

    /**
     * @return string
     */
    public function getRetraitement()
    {
        return $this->retraitement;
    }

    /**
     * @param string $retraitement
     * @return self
     */
    public function setRetraitement($retraitement)
    {
        $this->retraitement = $retraitement;

        return $this;
    }

    /**
     * @return boolean
     * @deprecated Redondant avec la version du fichier (version de diffusion)
     */
    public function getEstExpurge()
    {
        return $this->estExpurge;
    }

    /**
     * @param boolean $estExpurge
     * @return self
     * @deprecated Redondant avec la version du fichier (version de diffusion)
     */
    public function setEstExpurge($estExpurge = true)
    {
        $this->estExpurge = $estExpurge;

        return $this;
    }

    /**
     * Retourne :
     * - <code>1</code> si le thésard a certifié que le fichier de thèse était conforme ;
     * - <code>0</code> si le thésard a certifié que le fichier de thèse n'était PAS conforme ;
     * - <code>null</code> si le thésard n'a pas encore répondu à la question.
     *
     * @return null|int
     */
    public function getEstConforme()
    {
        return $this->estConforme;
    }

    /**
     * @return bool|null
     */
    public function getEstConformeToString()
    {
        if ($this->getEstConforme() === null) {
            return "";
        }

        return $this->getEstConforme() ? "Oui" : "Non";
    }

    /**
     * @param null|int $estConforme
     * @return self
     */
    public function setEstConforme($estConforme = null)
    {
        $this->estConforme = $estConforme;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEstPartiel()
    {
        return $this->estPartiel;
    }

    /**
     * @param bool $estPartiel
     * @return self
     */
    public function setEstPartiel($estPartiel = true)
    {
        $this->estPartiel = $estPartiel;

        return $this;
    }

    /**
     * @return Fichier
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * @param Fichier $fichier
     * @return FichierThese
     */
    public function setFichier(Fichier $fichier)
    {
        $this->fichier = $fichier;

        return $this;
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

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }
}




class FichierTheseFiltering
{
    /**
     * @param int|bool $estExpurge
     * @return \Closure
     */
    static public function getFilterByExpurge($estExpurge = true)
    {
        return function(FichierThese $fichier = null) use ($estExpurge) {
            if ($estExpurge === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            $actual = $fichier->getEstExpurge();
            $expected = (bool) $estExpurge;

            return $actual === $expected ? $fichier : null;
        };
    }

    /**
     * @param int|bool $estAnnexe
     * @return \Closure
     */
    static public function getFilterByAnnexe($estAnnexe = true)
    {
        return function(FichierThese $fichier = null) use ($estAnnexe) {
            if ($estAnnexe === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            $actual = $fichier->getEstAnnexe();
            $expected = (bool) $estAnnexe;

            return $actual === $expected ? $fichier : null;
        };
    }

    /**
     * @param int|bool|string $estRetraite '0', '1', booléen ou code du retraitement
     * @return \Closure
     */
    static public function getFilterByRetraitement($estRetraite = true)
    {
        return function(FichierThese $fichier = null) use ($estRetraite) {
            if ($estRetraite === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            $actual = $fichier->getRetraitement();
            $expected = $estRetraite;

            if (is_numeric($expected) || is_bool($expected)) {
                $expected = (bool) $estRetraite;
                $actual = (bool) $actual;
            }

            return $actual === $expected ? $fichier : null;
        };
    }

    /**
     * @param NatureFichier|string $nature
     * @return \Closure
     */
    static public function getFilterByNature($nature = null)
    {
        return function(FichierThese $fichier = null) use ($nature) {
            if ($nature === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            if ($nature instanceof NatureFichier) {
                $nature = $nature->getCode();
            }

            return $nature === $fichier->getFichier()->getNature()->getCode() ? $fichier : null;
        };
    }

    /**
     * @param VersionFichier|string $version
     * @return \Closure
     */
    static public function getFilterByVersion($version = null)
    {
        return function(FichierThese $fichier = null) use ($version) {
            if ($version === null) {
                return $fichier;
            }
            if ($fichier === null) {
                return null;
            }

            if ($version instanceof VersionFichier) {
                $version = $version->getCode();
            }

            return $version === $fichier->getFichier()->getVersion()->getCode() ? $fichier : null;
        };
    }
}