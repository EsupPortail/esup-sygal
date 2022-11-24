<?php

namespace Depot\Entity\Db\VSitu;

use Individu\Entity\Db\Individu;

/**
 * DepotVersionCorrigeeValidationPresident
 */
class DepotVersionCorrigeeValidationPresident
{
    /**
     * @var int
     */
    private $valide;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \These\Entity\Db\These
     */
    private $these;

    /**
     * @var \Individu\Entity\Db\Individu
     */
    private $individu;


    /**
     * Set valide
     *
     * @param int $valide
     *
     * @return DepotVersionCorrigeeValidationPresident
     */
    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return int
     */
    public function getValide()
    {
        return $this->valide;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return DepotVersionCorrigeeValidationPresident
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set these
     *
     * @param \These\Entity\Db\These $these
     *
     * @return DepotVersionCorrigeeValidationPresident
     */
    public function setThese(\These\Entity\Db\These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these
     *
     * @return \These\Entity\Db\These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * Set individu
     *
     * @param \Individu\Entity\Db\Individu|null $individu
     *
     * @return DepotVersionCorrigeeValidationPresident
     */
    public function setIndividu(Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Get individu
     *
     * @return \Individu\Entity\Db\Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }
}

