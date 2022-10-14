<?php

namespace Structure\Entity\Db;

class EtablissementRattachement {

    /** @var integer $id **/
    private $id;
    /** @var \Structure\Entity\Db\UniteRecherche $unite **/
    protected $unite;
    /** @var \Structure\Entity\Db\Etablissement $etablissement **/
    protected $etablissement;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
        if ($returnSubstitIfExists && $this->unite && ($sustitut = $this->unite->getUniteRechercheSubstituante())) {
            return $sustitut;
        }

        return $this->unite;
    }

    /**
     * @param \Structure\Entity\Db\UniteRecherche $unite
     * @return EtablissementRattachement
     */
    public function setUniteRecherche(UniteRecherche $unite): self
    {
        $this->unite = $unite;
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
     * @param \Structure\Entity\Db\Etablissement $etablissement
     * @return EtablissementRattachement
     */
    public function setEtablissement(Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }
}