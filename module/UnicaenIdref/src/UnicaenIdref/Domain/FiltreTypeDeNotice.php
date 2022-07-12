<?php

namespace UnicaenIdref\Domain;

class FiltreTypeDeNotice extends AbstractFiltre
{
    protected const VALUE_Personne = 'Personne';
    protected const VALUE_Collectivité = 'Collectivité';
    protected const VALUE_Famille = 'Famille';
    protected const VALUE_AuteurTitre = 'Auteur-Titre';
    protected const VALUE_Chronologie = 'Chronologie';
    protected const VALUE_Rameau = 'Rameau';
    protected const VALUE_Fmesh = 'Fmesh';
    protected const VALUE_Géographique = 'Géographique';
    protected const VALUE_Titre = 'Titre';
    protected const VALUE_Rcr = 'Rcr';
    protected const VALUE_Marque = 'Marque';

    protected string $filtre = 'Type de notice';

    public function setPersonne(): self
    {
        return $this->setFiltreValue(self::VALUE_Personne);
    }

    public function setCollectivité(): self
    {
        return $this->setFiltreValue(self::VALUE_Collectivité);
    }

    public function setFamille(): self
    {
        return $this->setFiltreValue(self::VALUE_Famille);
    }

    public function setAuteurTitre(): self
    {
        return $this->setFiltreValue(self::VALUE_AuteurTitre);
    }

    public function setChronologie(): self
    {
        return $this->setFiltreValue(self::VALUE_Chronologie);
    }

    public function setRameau(): self
    {
        return $this->setFiltreValue(self::VALUE_Rameau);
    }

    public function setFmesh(): self
    {
        return $this->setFiltreValue(self::VALUE_Fmesh);
    }

    public function setGéographique(): self
    {
        return $this->setFiltreValue(self::VALUE_Géographique);
    }

    public function setTitre(): self
    {
        return $this->setFiltreValue(self::VALUE_Titre);
    }

    public function setRcr(): self
    {
        return $this->setFiltreValue(self::VALUE_Rcr);
    }

    public function setMarque(): self
    {
        return $this->setFiltreValue(self::VALUE_Marque);
    }
}