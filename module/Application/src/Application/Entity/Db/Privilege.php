<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use UnicaenAuth\Entity\Db\AbstractPrivilege;

class Privilege extends AbstractPrivilege
{
    /** @var ArrayCollection */
    private $modeles;

    public function __construct()
    {
        parent::__construct();
        $this->modeles = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getRolesModeles()
    {
        return $this->modeles;
    }

    /**
     * @param RoleModele $modele
     * @return Privilege
     */
    public function addRoleModele($modele)
    {
        $this->modeles->add($modele);
        return $this;
    }

    /**
     * @param RoleModele $modele
     * @return Privilege
     */
    public function removeRoleModele($modele)
    {
        $this->modeles->removeElement($modele);
        return $this;
    }

    /**
     * @param RoleModele $modele
     * @return bool
     */
    public function hasRoleModele($modele)
    {
        return $this->modeles->contains($modele);
    }
}
