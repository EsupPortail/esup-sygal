<?php

namespace Notification\Entity;

/**
 *
 *
 * @author Unicaen
 */
class Notif
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $destinataires;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var integer
     */
    private $id;

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return static
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $description
     *
     * @return static
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
    public function getDestinataires()
    {
        return $this->destinataires;
    }

    /**
     * @param string $destinataires
     * @return Notif
     */
    public function setDestinataires($destinataires)
    {
        $this->destinataires = $destinataires;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return Notif
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return Notif
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

}