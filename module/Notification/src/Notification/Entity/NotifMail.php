<?php

namespace Notification\Entity;

use DateTime;

class NotifMail {

    /** @var integer */
    private $id;
    /** @var string|null */
    private $from;
    /** @var string|null */
    private $to;
    /** @var string|null */
    private $subject;
    /** @var string|null */
    private $body;
    /** @var DateTime|null */
    private $sentOn;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return NotifMail
     */
    public function setId(int $id): NotifMail
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @param string|null $from
     * @return NotifMail
     */
    public function setFrom(?string $from): NotifMail
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * @param string|null $to
     * @return NotifMail
     */
    public function setTo(?string $to): NotifMail
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     * @return NotifMail
     */
    public function setSubject(?string $subject): NotifMail
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     * @return NotifMail
     */
    public function setBody(?string $body): NotifMail
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getSentOn(): ?DateTime
    {
        return $this->sentOn;
    }

    /**
     * @param DateTime|null $sentOn
     * @return NotifMail
     */
    public function setSentOn(?DateTime $sentOn): NotifMail
    {
        $this->sentOn = $sentOn;
        return $this;
    }

}
