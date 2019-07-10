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
        $fichier = $this->getFichier();

        return $fichier->getNature()->estThesePdf() && ! $fichier->getVersion()->estVersionDiffusion();
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
