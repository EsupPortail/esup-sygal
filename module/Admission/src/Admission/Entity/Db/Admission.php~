<?php
namespace Admission\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Admission implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var int|null
     */
    private $etatId;

    /**
     * @var int
     */
    private $id;

    /**
     * Set etatId.
     *
     * @param int|null $etatId
     *
     * @return Admission
     */
    public function setEtatId($etatId = null)
    {
        $this->etatId = $etatId;

        return $this;
    }

    /**
     * Get etatId.
     *
     * @return int|null
     */
    public function getEtatId()
    {
        return $this->etatId;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
