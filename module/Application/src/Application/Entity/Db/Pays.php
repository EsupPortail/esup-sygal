<?php

namespace Application\Entity\Db;

class Pays
{
    private int $id;
    private string $codeIso;
    private string $codeIsoAlpha3;
    private string $codeIsoAlpha2;
    private string $libelle;
    private string $libelleIso;
    private ?string $libelleNationalite;
    private ?string $codePaysApogee;

    public function __toString(): string
    {
        return $this->getLibelle();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCodeIso(): string
    {
        return $this->codeIso;
    }

    /**
     * @param string $codeIso
     * @return self
     */
    public function setCodeIso(string $codeIso): self
    {
        $this->codeIso = $codeIso;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeIsoAlpha3(): string
    {
        return $this->codeIsoAlpha3;
    }

    /**
     * @param string $codeIsoAlpha3
     * @return self
     */
    public function setCodeIsoAlpha3(string $codeIsoAlpha3): self
    {
        $this->codeIsoAlpha3 = $codeIsoAlpha3;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeIsoAlpha2(): string
    {
        return $this->codeIsoAlpha2;
    }

    /**
     * @param string $codeIsoAlpha2
     * @return self
     */
    public function setCodeIsoAlpha2(string $codeIsoAlpha2): self
    {
        $this->codeIsoAlpha2 = $codeIsoAlpha2;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return self
     */
    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleIso(): string
    {
        return $this->libelleIso;
    }

    /**
     * @param string $libelleIso
     * @return self
     */
    public function setLibelleIso(string $libelleIso): self
    {
        $this->libelleIso = $libelleIso;
        return $this;
    }

    public function getLibelleNationalite(): ?string
    {
        return $this->libelleNationalite;
    }

    /**
     * @param string $libelleNationalite
     * @return self
     */
    public function setLibelleNationalite(string $libelleNationalite): self
    {
        $this->libelleNationalite = $libelleNationalite;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodePaysApogee(): string
    {
        return $this->codePaysApogee;
    }

    /**
     * @param string $codePaysApogee
     * @return self
     */
    public function setCodePaysApogee(string $codePaysApogee): self
    {
        $this->codePaysApogee = $codePaysApogee;
        return $this;
    }
}