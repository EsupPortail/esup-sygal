<?php

namespace Structure\Entity\Db;

use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

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
     * Retourne l'éventuelle structure liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.structure' puis 'structure.structureSubstituante'.
     *
     * @param bool $returnSubstitIfExists À true, retourne la structure substituante s'il y en a une ; sinon la structure d'origine.
     * @see Structure::getStructureSubstituante()
     * @return Structure|null
     */
    public function getStructure(bool $returnSubstitIfExists = true): ?Structure
    {
        if ($returnSubstitIfExists && $this->structure && ($sustitut = $this->structure->getStructureSubstituante())) {
            return $sustitut;
        }

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
     * Retourne l'éventuel établissement lié *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.etablissement' puis 'etablissement.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.etablissement'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'établissement substituant s'il y en a un ; sinon l'établissement d'origine.
     * @see Etablissement::getEtablissementSubstituant()
     * @return Etablissement|null
     */
    public function getEtablissement(bool $returnSubstitIfExists = true): ?Etablissement
    {
        if ($returnSubstitIfExists && $this->etablissement && ($sustitut = $this->etablissement->getEtablissementSubstituant())) {
            return $sustitut;
        }

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