<?php

namespace Individu\Entity\Db;

use Structure\Entity\Db\Etablissement;

class IndividuRoleEtablissement
{
    private int $id;
    private IndividuRole $individuRole;
    private ?Etablissement $etablissement = null;

    static public function sorter(): callable
    {
        return fn(IndividuRoleEtablissement $a, IndividuRoleEtablissement $b) =>
            $a->getEtablissement()->getStructure()->getLibelle() <=>
            $b->getEtablissement()->getStructure()->getLibelle();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIndividuRole(): IndividuRole
    {
        return $this->individuRole;
    }

    public function setIndividuRole(IndividuRole $individuRole): self
    {
        $this->individuRole = $individuRole;
        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }
}