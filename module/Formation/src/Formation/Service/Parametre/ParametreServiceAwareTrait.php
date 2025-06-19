<?php

namespace Formation\Service\Parametre;

trait ParametreServiceAwareTrait {

    private ParametreService $parametreService;

    /**
     * @return ParametreService
     */
    public function getParametreService(): ParametreService
    {
        return $this->parametreService;
    }

    /**
     * @param ParametreService $parametreService
     */
    public function setParametreService(ParametreService $parametreService): void
    {
        $this->parametreService = $parametreService;
    }
}