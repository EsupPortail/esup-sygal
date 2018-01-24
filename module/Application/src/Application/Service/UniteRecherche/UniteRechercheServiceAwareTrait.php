<?php

namespace Application\Service\UniteRecherche;

trait UniteRechercheServiceAwareTrait
{
    /**
     * @var UniteRechercheService
     */
    protected $uniteRechercheService;

    /**
     * @param UniteRechercheService $uniteRechercheService
     */
    public function setUniteRechercheService(UniteRechercheService $uniteRechercheService)
    {
        $this->uniteRechercheService = $uniteRechercheService;
    }
}