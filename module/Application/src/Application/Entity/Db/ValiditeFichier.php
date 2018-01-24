<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * ValiditeFichier
 */
class ValiditeFichier implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var bool
     */
    private $estValide;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $log;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Application\Entity\Db\Fichier
     */
    private $fichier;


    /**
     * Set estValide
     *
     * @param bool|null $estValide
     *
     * @return ValiditeFichier
     */
    public function setEstValide($estValide = true)
    {
        $this->estValide = $estValide;

        return $this;
    }

    /**
     * Get estValide
     *
     * @return bool|null
     * <code>true</code>  : archivable ;
     * <code>false</code> : non archivable ;
     * <code>null</code>  : archivabilité indéterminée car plantage lors du test.
     */
    public function getEstValide()
    {
        return $this->estValide;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return ValiditeFichier
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
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
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param string $log
     * @return ValiditeFichier
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Set fichier
     *
     * @param \Application\Entity\Db\Fichier $fichier
     *
     * @return ValiditeFichier
     */
    public function setFichier(\Application\Entity\Db\Fichier $fichier = null)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return \Application\Entity\Db\Fichier
     */
    public function getFichier()
    {
        return $this->fichier;
    }
}

