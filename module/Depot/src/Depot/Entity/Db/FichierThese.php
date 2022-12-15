<?php

namespace Depot\Entity\Db;

use Fichier\Entity\Db\Fichier;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use These\Entity\Db\These;

/**
 * Classe représentant un fichier lié *à une thèse*.
 *
 * @todo : déplacer de cette classe ce qui concerne le retraitement et la conformité.
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
     * @var string
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    private $retraitement;

    /**
     * @var null|int
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    private $estConforme = null;

    /**
     * @var bool
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
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
     * Détermine si ce fichier peut faire l'objet d'un test de validité (ex : archivabilité).
     *
     * @return bool
     */
    public function supporteTestValidite(): bool
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
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    public function isRetraitementAuto()
    {
        return $this->getRetraitement() === self::RETRAITEMENT_AUTO;
    }

    /**
     * @return bool
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    public function isRetraitementManuel()
    {
        return $this->getRetraitement() === self::RETRAITEMENT_MANU;
    }

    /**
     * @return string
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    public function getRetraitement()
    {
        return $this->retraitement;
    }

    /**
     * @param string $retraitement
     * @return self
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
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
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    public function getEstConforme()
    {
        return $this->estConforme;
    }

    /**
     * @return bool|null
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
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
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    public function setEstConforme($estConforme = null)
    {
        $this->estConforme = $estConforme;

        return $this;
    }

    /**
     * @return bool
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
     */
    public function getEstPartiel()
    {
        return $this->estPartiel;
    }

    /**
     * @param bool $estPartiel
     * @return self
     * @todo : supprimer de cette classe ce qui concerne le retraitement et la conformité.
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
