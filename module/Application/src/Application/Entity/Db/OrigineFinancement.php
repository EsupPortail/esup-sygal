<?php

namespace Application\Entity\Db;

use Application\Search\Filter\SearchFilterValueInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

class OrigineFinancement implements ResourceInterface, SearchFilterValueInterface
{
    use HistoriqueAwareTrait;

    const CODE_REGION_NORMANDIE = '24';

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
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelleLong();
    }

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

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        return ['value' => $this->getCode(), 'label' => $this->getLibelleLong()];
    }
}