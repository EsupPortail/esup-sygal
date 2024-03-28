<?php

namespace Individu\Entity\Db;

use Application\Entity\Db\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
    protected Collection $individuRoleEtablissement;

    /**
     * Retourne la fonction de callback à utiliser pour trier une collection d'entités IndividuRole selon le rôle.
     */
    static public function sorter(): callable
    {
        return fn(IndividuRole $a1, IndividuRole $a2) =>
            $a1->getRole()->getOrdreAffichage() . $a1->getIndividu()->getNomUsuel() . $a1->getIndividu()->getPrenom() <=>
            $a2->getRole()->getOrdreAffichage() . $a2->getIndividu()->getNomUsuel() . $a2->getIndividu()->getPrenom();
    }

    public function __construct()
    {
        $this->individuRoleEtablissement = new ArrayCollection();
    }

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

    public function getIndividuRoleEtablissement(): Collection
    {
        $individuRoleEtablissement = $this->individuRoleEtablissement->toArray();
        usort($individuRoleEtablissement, IndividuRoleEtablissement::sorter());

        return new ArrayCollection($individuRoleEtablissement);
    }

    public function addIndividuRoleEtablissement(Collection $individuRoleEtablissement): void
    {
        $individuRoleEtablissement->forAll(
            fn($key, IndividuRoleEtablissement $ire) => $this->individuRoleEtablissement->add($ire)
        );
    }

    public function removeIndividuRoleEtablissement(Collection $individuRoleEtablissement): void
    {
        $individuRoleEtablissement->forAll(
            fn($key, IndividuRoleEtablissement $ire) => $this->individuRoleEtablissement->removeElement($ire)
        );
    }

    public function getIndividuRoleEtablissementToString(string $separator = ', ', callable $entityFormatterClosure = null): string
    {
        if ($entityFormatterClosure === null) {
            $entityFormatterClosure =
                fn(IndividuRoleEtablissement $irp) => $irp->getEtablissement()->getStructure()->getSigle();
        }

        return implode(
            $separator,
            $this->individuRoleEtablissement->map($entityFormatterClosure)->toArray()
        );
    }
}