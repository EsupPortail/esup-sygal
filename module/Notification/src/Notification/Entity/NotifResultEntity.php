<?php

namespace Notification\Entity;

use DateTime;

/**
 *
 *
 * @author Unicaen
 */
class NotifResultEntity
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $error;

    /**
     * @var DateTime
     */
    private $sentOn;

    /**
     * @var NotifEntity
     */
    private $notif;

    /**
     * @var integer
     */
    private $id;

    /**
     * Set code
     *
     * @param string $subject
     *
     * @return static
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set libelle
     *
     * @param string $body
     *
     * @return static
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
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
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return self
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getSentOn()
    {
        return $this->sentOn;
    }

    /**
     * @param DateTime $sentOn
     * @return self
     */
    public function setSentOn(DateTime $sentOn)
    {
        $this->sentOn = $sentOn;

        return $this;
    }

    /**
     * @return NotifEntity
     */
    public function getNotif()
    {
        return $this->notif;
    }

    /**
     * @param NotifEntity $notif
     * @return static
     */
    public function setNotif(NotifEntity $notif)
    {
        $this->notif = $notif;

        return $this;
    }



}