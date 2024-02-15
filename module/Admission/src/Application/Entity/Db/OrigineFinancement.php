<?php

namespace Application\Entity\Db;

/**
 * OrigineFinancement
 */
class OrigineFinancement
{
    /**
     * @var string|null
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $libelleLong;

    /**
     * @var string
     */
    private $libelleCourt;

    /**
     * @var bool
     */
    private $visible;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Application\Entity\Db\Source
     */
    private $source;


    /**
     * Set sourceCode.
     *
     * @param string|null $sourceCode
     *
     * @return OrigineFinancement
     */
    public function setSourceCode($sourceCode = null)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode.
     *
     * @return string|null
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return OrigineFinancement
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelleLong.
     *
     * @param string $libelleLong
     *
     * @return OrigineFinancement
     */
    public function setLibelleLong($libelleLong)
    {
        $this->libelleLong = $libelleLong;

        return $this;
    }

    /**
     * Get libelleLong.
     *
     * @return string
     */
    public function getLibelleLong()
    {
        return $this->libelleLong;
    }

    /**
     * Set libelleCourt.
     *
     * @param string $libelleCourt
     *
     * @return OrigineFinancement
     */
    public function setLibelleCourt($libelleCourt)
    {
        $this->libelleCourt = $libelleCourt;

        return $this;
    }

    /**
     * Get libelleCourt.
     *
     * @return string
     */
    public function getLibelleCourt()
    {
        return $this->libelleCourt;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return OrigineFinancement
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
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

    /**
     * Set source.
     *
     * @param \Application\Entity\Db\Source|null $source
     *
     * @return OrigineFinancement
     */
    public function setSource(\Application\Entity\Db\Source $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return \Application\Entity\Db\Source|null
     */
    public function getSource()
    {
        return $this->source;
    }
}
