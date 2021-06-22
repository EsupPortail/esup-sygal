<?php

namespace Application\Entity\Db\VSitu;

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
     * @var \Application\Entity\Db\These
     */
    private $these;

    /**
     * @var \Application\Entity\Db\Individu
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
     * @param \Application\Entity\Db\These $these
     *
     * @return DepotVersionCorrigeeValidationPresident
     */
    public function setThese(\Application\Entity\Db\These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these
     *
     * @return \Application\Entity\Db\These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * Set individu
     *
     * @param \Application\Entity\Db\Individu $individu
     *
     * @return DepotVersionCorrigeeValidationPresident
     */
    public function setIndividu(\Application\Entity\Db\Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Get individu
     *
     * @return \Application\Entity\Db\Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }
}

