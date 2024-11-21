<?php

namespace Structure\Entity\Db;

use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class StructureDocument implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;

    /**
     * @var NatureFichier|null
     * @deprecated À supprimer car redondant avec {@see \Fichier\Entity\Db\Fichier::$nature}
     */
    private $nature;

    /** @var Structure|null */
    private $structure;
    /** @var Etablissement|null */
    private $etablissement;
    /** @var Fichier|null */
    private $fichier;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return StructureDocument
     */
    public function setId(int $id): StructureDocument
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return NatureFichier|null
     * @deprecated À supprimer car redondant avec {@see \Fichier\Entity\Db\Fichier::getNature()}
     */
    public function getNature(): ?NatureFichier
    {
        return $this->nature;
    }

    /**
     * @param NatureFichier|null $nature
     * @return StructureDocument
     * @deprecated À supprimer car redondant avec {@see \Fichier\Entity\Db\Fichier::setNature()}
     */
    public function setNature(?NatureFichier $nature): StructureDocument
    {
        $this->nature = $nature;
        return $this;
    }

    /**
     * Retourne l'éventuelle structure liée.
     */
    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    /**
     * @param Structure|null $structure
     * @return StructureDocument
     */
    public function setStructure(?Structure $structure): StructureDocument
    {
        $this->structure = $structure;
        return $this;
    }

    /**
     * Retourne l'éventuel établissement lié.
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return StructureDocument
     */
    public function setEtablissement(?Etablissement $etablissement): StructureDocument
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return Fichier|null
     */
    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    /**
     * @param Fichier|null $fichier
     * @return StructureDocument
     */
    public function setFichier(?Fichier $fichier): StructureDocument
    {
        $this->fichier = $fichier;
        return $this;
    }
}