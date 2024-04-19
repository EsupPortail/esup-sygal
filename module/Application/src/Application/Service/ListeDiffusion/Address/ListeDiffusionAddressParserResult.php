<?php

namespace Application\Service\ListeDiffusion\Address;

class ListeDiffusionAddressParserResult
{
    /**
     * Code du rôle concerné en minuscule (ex: 'admin_tech', 'bu', 'bdd') ;
     * ou alias de rôle (ex: 'doctorants', 'dirtheses').
     */
    protected string $role;

    /**
     * Code de l'établissement concerné éventuel.
     */
    protected ?string $etablissement = null;

    /**
     * Retourne le code du rôle concerné en minuscule.
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Spécifie le code du rôle concerné en minuscule.
     */
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Retourne le code de l'établissement concerné éventuel.
     */
    public function getEtablissement(): ?string
    {
        return $this->etablissement;
    }

    /**
     * Spécifie le code de l'établissement concerné.
     */
    public function setEtablissement(?string $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }

}