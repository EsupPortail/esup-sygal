<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class OrigineFinancement implements ResourceInterface
{
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;
    /** @var string */
    private $code;
    /** @var string */
    private $libelleLong;
    /** @var string */
    private $libelleCourt;
    /** @var Source */
    private $source;
    /** @var string */
    private $sourceCode;
    /** @var bool */
    private $visible;

    /**
     * @inheritDoc
     */
    public function getResourceId(): string
    {
        return 'OrigineFinancement';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return OrigineFinancement
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleLong()
    {
        return $this->libelleLong;
    }

    /**
     * @param string $libelleLong
     * @return OrigineFinancement
     */
    public function setLibelleLong($libelleLong)
    {
        $this->libelleLong = $libelleLong;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleCourt()
    {
        return $this->libelleCourt;
    }

    /**
     * @param string $libelleCourt
     * @return OrigineFinancement
     */
    public function setLibelleCourt($libelleCourt)
    {
        $this->libelleCourt = $libelleCourt;
        return $this;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Source $source
     * @return OrigineFinancement
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @param string $sourceCode
     * @return OrigineFinancement
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return self
     */
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }
}