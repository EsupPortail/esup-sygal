<?php

namespace Individu\Entity\Db;

use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class IndividuCompl implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int $id */
    private $id;
    /** @var Individu $individu */
    private $individu;
    /** @var string $email */
    private $email;
    /** @var Etablissement $etablissement */
    private $etablissement;
    /** @var UniteRecherche $uniteRecherche */
    private $uniteRecherche;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Individu|null
     */
    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return IndividuCompl
     */
    public function setIndividu(Individu $individu): IndividuCompl
    {
        $this->individu = $individu;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return IndividuCompl
     */
    public function setEmail(string $email): IndividuCompl
    {
        $this->email = $email;
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
     */
    public function setEtablissement(?Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    /**
     * Retourne l'éventuelle UR liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.uniteRecherche' puis 'uniteRecherche.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.uniteRecherche'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'UR substituante s'il y en a une ; sinon l'UR d'origine.
     * @see UniteRecherche::getUniteRechercheSubstituante()
     * @return UniteRecherche|null
     */
    public function getUniteRecherche(bool $returnSubstitIfExists = true): ?UniteRecherche
    {
        if ($returnSubstitIfExists && $this->uniteRecherche && ($sustitut = $this->uniteRecherche->getUniteRechercheSubstituante())) {
            return $sustitut;
        }

        return $this->uniteRecherche;
    }

    /**
     * @param UniteRecherche|null $uniteRecherche
     */
    public function setUniteRecherche(?UniteRecherche $uniteRecherche): void
    {
        $this->uniteRecherche = $uniteRecherche;
    }

}