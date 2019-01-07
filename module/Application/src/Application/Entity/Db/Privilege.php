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
     * @param Profil $profil
     * @return Privilege
     */
    public function addProfil($profil)
    {
        $this->modeles->add($profil);
        return $this;
    }

    /**
     * @param Profil $profil
     * @return Privilege
     */
    public function removeProfil($profil)
    {
        $this->modeles->removeElement($profil);
        return $this;
    }

    /**
     * @param Profil $profil
     * @return bool
     */
    public function hasProfil($profil)
    {
        return $this->modeles->contains($profil);
    }
}
