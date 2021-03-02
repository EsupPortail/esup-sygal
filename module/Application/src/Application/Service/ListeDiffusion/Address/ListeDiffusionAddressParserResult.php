<?php

namespace Application\Service\ListeDiffusion\Address;

class ListeDiffusionAddressParserResult
{
    /**
     * Code du rôle concerné éventuel en minuscule (ex: 'admin_tech', 'bu', 'bdd') ;
     * ou alias de rôle (ex: 'doctorants', 'dirtheses').
     *
     * @var string
     */
    protected $role;

    /**
     * Code de l'établissement concerné éventuel.
     *
     * @var string
     */
    protected $etablissement;

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return ListeDiffusionAddressParserResult
     */
    public function setRole(string $role): ListeDiffusionAddressParserResult
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEtablissement(): ?string
    {
        return $this->etablissement;
    }

    /**
     * @param string|null $etablissement
     * @return ListeDiffusionAddressParserResult
     */
    public function setEtablissement(?string $etablissement = null): ListeDiffusionAddressParserResult
    {
        $this->etablissement = $etablissement;
        return $this;
    }

}