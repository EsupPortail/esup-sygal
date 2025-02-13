<?php

namespace Depot\Entity\Db;

use Fichier\Entity\Db\Fichier;
use HDR\Entity\Db\HDR;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * Classe représentant un fichier lié *à une HDR*.
 *
 * @todo : déplacer de cette classe ce qui concerne le retraitement et la conformité.
 */
class FichierHDR implements ResourceInterface, HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    /**
     * Tag marquant un fichier qui résulte d'un deuxième dépôt (après correction).
     *
     * 'V2' = Deuxième dépôt (après corrections)
     */
    const TAG_DEUXIEME_DEPOT = 'V2';

    const RESOURCE_ID = 'FichierHDR';

    const MESSAGE_DEPOT_DUREE = "L'opération de téléversement de fichier peut durer quelques minutes.";

    /**
     * @var string
     */
    private $id;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * @var HDR
     */
    private $hdr;

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
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @return FichierHDR
     */
    public function setFichier(Fichier $fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * @return HDR
     */
    public function getHDR()
    {
        return $this->hdr;
    }

    /**
     * @param HDR $hdr
     * @return self
     */
    public function setHDR($hdr)
    {
        $this->hdr = $hdr;

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
