<?php

namespace Structure\Service\UniteRecherche;

trait UniteRechercheServiceAwareTrait
{
    /**
     * @var UniteRechercheService
     */
    protected $uniteRechercheService;

    /**
     * @return UniteRechercheService
     */
    public function getUniteRechercheService()
    {
        return $this->uniteRechercheService;
    }

    /**
     * @param UniteRechercheService $uniteRechercheService
     */
    public function setUniteRechercheService(UniteRechercheService $uniteRechercheService)
    {
        $this->uniteRechercheService = $uniteRechercheService;
    }
}