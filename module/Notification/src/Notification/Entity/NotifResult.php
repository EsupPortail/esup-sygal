<?php

namespace Notification\Entity;

/**
 *
 *
 * @author Unicaen
 */
class NotifResult
{
    /**
     * @var string
     */
    private $sujet;

    /**
     * @var string
     */
    private $corps;

    /**
     * @var string
     */
    private $erreur;

    /**
     * @var \DateTime
     */
    private $dateEnvoi;

    /**
     * @var Notif
     */
    private $notif;

    /**
     * @var integer
     */
    private $id;

    /**
     * Set code
     *
     * @param string $sujet
     *
     * @return static
     */
    public function setSujet($sujet)
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getSujet()
    {
        return $this->sujet;
    }

    /**
     * Set libelle
     *
     * @param string $corps
     *
     * @return static
     */
    public function setCorps($corps)
    {
        $this->corps = $corps;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getCorps()
    {
        return $this->corps;
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
    public function getErreur()
    {
        return $this->erreur;
    }

    /**
     * @param string $erreur
     * @return self
     */
    public function setErreur($erreur)
    {
        $this->erreur = $erreur;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateEnvoi()
    {
        return $this->dateEnvoi;
    }

    /**
     * @param string $dateEnvoi
     * @return self
     */
    public function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    /**
     * @return Notif
     */
    public function getNotif()
    {
        return $this->notif;
    }

    /**
     * @param Notif $notif
     * @return NotifResult
     */
    public function setNotif(Notif $notif)
    {
        $this->notif = $notif;

        return $this;
    }



}