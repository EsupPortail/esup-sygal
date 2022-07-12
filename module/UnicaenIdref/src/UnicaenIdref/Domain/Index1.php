<?php

namespace UnicaenIdref\Domain;

class Index1 extends AbstractIndex
{
    protected string $name = 'Index1';
    protected string $valueName = 'Index1Value';

    /**
     * ATTENTION !
     * L'intitulé des index ci-dessous doit correspondre exactement au label (ou à l'attribut HTML `data-text` du label ?)
     * des boutons radios visibles dans le cadre "Type d’autorité" de l'interface web (iframe) d'IdRef.
     */
    public const INDEX_NomDePersonne = 'Nom de personne';
    public const INDEX_NomDeCollectivité = 'Nom de collectivité';
    public const INDEX_Congres = 'Congrès';
    public const INDEX_NomCommun = 'Nom commun';
    public const INDEX_Rameau = 'Forme ou genre Rameau';
    public const INDEX_NomGéographique = 'Nom géographique';
    public const INDEX_Famille = 'Famille';
    public const INDEX_Titre = 'Titre';
    public const INDEX_AuteurTitre = 'Auteur-Titre';
    public const INDEX_NomDeMarque = 'Nom de marque';
    public const INDEX_Ppn = 'Identifiant IdRef (n°PPN)';
    public const INDEX_Rcr = 'Établissement documentaire ou n°RCR';
    public const INDEX_Tout = 'Tous les index';

    public function __construct()
    {
        $this->index = '';
        $this->indexValue = '';
    }

    public function setNomDePersonne(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_NomDePersonne)
            ->setIndexValue($indexValue);
    }
    public function setNomDeCollectivité(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_NomDeCollectivité)
            ->setIndexValue($indexValue);
    }
    public function setCongres(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Congres)
            ->setIndexValue($indexValue);
    }
    public function setNomCommun(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_NomCommun)
            ->setIndexValue($indexValue);
    }
    public function setRameau(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Rameau)
            ->setIndexValue($indexValue);
    }
    public function setNomGéographique(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_NomGéographique)
            ->setIndexValue($indexValue);
    }
    public function setFamille(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Famille)
            ->setIndexValue($indexValue);
    }
    public function setTitre(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Titre)
            ->setIndexValue($indexValue);
    }
    public function setAuteurTitre(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_AuteurTitre)
            ->setIndexValue($indexValue);
    }
    public function setNomDeMarque(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_NomDeMarque)
            ->setIndexValue($indexValue);
    }
    public function setPpn(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Ppn)
            ->setIndexValue($indexValue);
    }
    public function setRcr(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Rcr)
            ->setIndexValue($indexValue);
    }
    public function setTout(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Tout)
            ->setIndexValue($indexValue);
    }

}