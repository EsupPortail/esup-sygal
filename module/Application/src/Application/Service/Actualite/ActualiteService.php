<?php

namespace Application\Service\Actualite;

class ActualiteService
{
    /**
     * @var bool
     */
    private $actif = false;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @return bool
     */
    public function isActif(): bool
    {
        return $this->actif;
    }

    /**
     * @param bool $actif
     * @return ActualiteService
     */
    public function setActif(bool $actif = true): ActualiteService
    {
        $this->actif = $actif;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return ActualiteService
     */
    public function setUrl(?string $url = null): ActualiteService
    {
        $this->url = $url;
        return $this;
    }
}