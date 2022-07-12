<?php

namespace UnicaenIdref;

use UnicaenIdref\Domain\AbstractFiltre;
use UnicaenIdref\Domain\Index1;
use UnicaenIdref\Domain\Index2;
use UnicaenIdref\Domain\Index3;

class Params
{
    protected ?string $fromApp = null;

    protected Index1 $index1;
    protected ?Index2 $index2 = null;
    protected ?Index3 $index3 = null;

    protected ?AbstractFiltre $filtre1 = null;
    protected ?AbstractFiltre $filtre2 = null;
    protected ?AbstractFiltre $filtre3 = null;
    protected ?AbstractFiltre $filtre4 = null;

    protected ?string $zones = null;

    public function __construct()
    {
        $this->index1 = new Index1();
    }

    /**
     * @return string|null
     */
    public function getFromApp(): ?string
    {
        return $this->fromApp;
    }

    /**
     * @param string $fromApp
     * @return self
     */
    public function setFromApp(string $fromApp): self
    {
        $this->fromApp = $fromApp;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\Index1
     */
    public function getIndex1(): Index1
    {
        return $this->index1;
    }

    /**
     * @param \UnicaenIdref\Domain\Index1 $index1
     * @return self
     */
    public function setIndex1(Index1 $index1): self
    {
        $this->index1 = $index1;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\Index2|null
     */
    public function getIndex2(): ?Index2
    {
        return $this->index2;
    }

    /**
     * @param \UnicaenIdref\Domain\Index2|null $index2
     * @return self
     */
    public function setIndex2(?Index2 $index2): self
    {
        $this->index2 = $index2;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\Index3|null
     */
    public function getIndex3(): ?Index3
    {
        return $this->index3;
    }

    /**
     * @param \UnicaenIdref\Domain\Index3|null $index3
     * @return self
     */
    public function setIndex3(?Index3 $index3): self
    {
        $this->index3 = $index3;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\AbstractFiltre|null
     */
    public function getFiltre1(): ?AbstractFiltre
    {
        return $this->filtre1;
    }

    /**
     * @param \UnicaenIdref\Domain\AbstractFiltre|null $filtre1
     * @return self
     */
    public function setFiltre1(?AbstractFiltre $filtre1): self
    {
        $this->filtre1 = $filtre1;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\AbstractFiltre|null
     */
    public function getFiltre2(): ?AbstractFiltre
    {
        return $this->filtre2;
    }

    /**
     * @param \UnicaenIdref\Domain\AbstractFiltre|null $filtre2
     * @return self
     */
    public function setFiltre2(?AbstractFiltre $filtre2): self
    {
        $this->filtre2 = $filtre2;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\AbstractFiltre|null
     */
    public function getFiltre3(): ?AbstractFiltre
    {
        return $this->filtre3;
    }

    /**
     * @param \UnicaenIdref\Domain\AbstractFiltre|null $filtre3
     * @return self
     */
    public function setFiltre3(?AbstractFiltre $filtre3): self
    {
        $this->filtre3 = $filtre3;
        return $this;
    }

    /**
     * @return \UnicaenIdref\Domain\AbstractFiltre|null
     */
    public function getFiltre4(): ?AbstractFiltre
    {
        return $this->filtre4;
    }

    /**
     * @param \UnicaenIdref\Domain\AbstractFiltre|null $filtre4
     * @return self
     */
    public function setFiltre4(?AbstractFiltre $filtre4): self
    {
        $this->filtre4 = $filtre4;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getZones(): ?string
    {
        return $this->zones;
    }

    /**
     * @param string|null $zones
     * @return self
     */
    public function setZones(?string $zones): self
    {
        $this->zones = $zones;
        return $this;
    }

}