<?php

namespace Individu\Entity\Db;

use Application\Entity\Db\Role;

class IndividuRole implements IndividuRoleAwareInterface
{
    /**
     * @var int $id
     * @var \Individu\Entity\Db\Individu $individu
     * @var \Application\Entity\Db\Role $role
     */
    private $id;
    protected $individu;
    protected $role;

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    public function setIndividu(?Individu $individu = null): self
    {
        $this->individu = $individu;
        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Retourne la fonction de callback à utiliser pour trier une collection d'entités IndividuRole selon le rôle.
     * @see usort()
     *
     * @return callable
     */
    static public function getComparisonFunction()
    {
        return function(IndividuRole $a1, IndividuRole $a2) {
            return strcmp(
                $a1->getRole()->getOrdreAffichage() . $a1->getIndividu()->getNomUsuel() . $a1->getIndividu()->getPrenom(),
                $a2->getRole()->getOrdreAffichage() . $a2->getIndividu()->getNomUsuel() . $a2->getIndividu()->getPrenom()
            );
        };
    }
}