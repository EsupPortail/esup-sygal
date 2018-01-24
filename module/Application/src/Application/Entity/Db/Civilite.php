<?php

namespace Application\Entity\Db;

use Doctrine\ORM\Mapping as ORM;


/**
 * Civilite
 */
class Civilite
{
    /**
     * @var string
     */
    protected $libelleCourt;

    /**
     * @var string
     */
    protected $libelleLong;

    /**
     * @var string
     */
    protected $sexe;

    /**
     * @var integer
     */
    protected $id;

    public function __toString()
    {
        return $this->getLibelleCourt();
    }
    
    /**
     * Set libelleCourt
     *
     * @param string $libelleCourt
     * @return Civilite
     */
    public function setLibelleCourt($libelleCourt)
    {
        $this->libelleCourt = $libelleCourt;

        return $this;
    }

    /**
     * Get libelleCourt
     *
     * @return string 
     */
    public function getLibelleCourt()
    {
        return $this->libelleCourt;
    }

    /**
     * Set libelleLong
     *
     * @param string $libelleLong
     * @return Civilite
     */
    public function setLibelleLong($libelleLong)
    {
        $this->libelleLong = $libelleLong;

        return $this;
    }

    /**
     * Get libelleLong
     *
     * @return string 
     */
    public function getLibelleLong()
    {
        return $this->libelleLong;
    }

    /**
     * Set sexe
     *
     * @param string $sexe
     * @return Civilite
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe
     *
     * @return string 
     */
    public function getSexe()
    {
        return $this->sexe;
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


	/**************************************************************************************************
	 * 										Début ajout
	 **************************************************************************************************/

    /**
     * @since PHP 5.6.0
     * This method is called by var_dump() when dumping an object to get the properties that should be shown.
     * If the method isn't defined on an object, then all public, protected and private properties will be shown.
     *
     * @return array
     * @link  http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     */
    function __debugInfo()
    {
        return [
            'id' => $this->id,
            'libelleLong' => $this->libelleLong
        ];
    }
}
