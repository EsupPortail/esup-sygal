<?php

namespace HDR\Service\FichierHDR;

class MembreData
{
    private $denomination;
    private $qualite;
    private $etablissement;
    private $role;

    /**
     * @return mixed
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * @param mixed $denomination
     * @return MembreData
     */
    public function setDenomination($denomination)
    {
        $this->denomination = $denomination;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQualite()
    {
        return $this->qualite;
    }

    /**
     * @param mixed $qualite
     * @return MembreData
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param mixed $etablissement
     * @return MembreData
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     * @return MembreData
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }
}
