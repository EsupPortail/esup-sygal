<?php

namespace Application\Service\Actualite;

class ActualiteService
{
    /**
     * @var bool
     */
    private $actif = false;

    /**
     * @var bool
     */
    private $offre = false;

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
     * @return bool
     */
    public function isOffre(): bool
    {
        return $this->offre;
    }

    /**
     * @param bool $offre
     * @return ActualiteService
     */
    public function setOffre(bool $offre = true): ActualiteService
    {
        $this->offre = $offre;
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

    public function isSoutenance() : bool
    {
        return true;
    }
}