<?php

namespace Formation\Entity\Db;

use Structure\Entity\Db\Structure;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class SessionStructureValide implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    private int $id;
    private ?Session $session = null;
    private ?Structure $structure = null;
    private ?string $lieu = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @param Session|null $session
     */
    public function setSession(?Session $session): void
    {
        $this->session = $session;
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
     */
    public function setStructure(?Structure $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @return string|null
     */
    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    /**
     * @param string|null $lieu
     */
    public function setLieu(?string $lieu): void
    {
        $this->lieu = $lieu;
    }

}