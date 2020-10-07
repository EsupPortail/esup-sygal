<?php

namespace Soutenance\Service\Parametre;

trait ParametreServiceAwareTrait {
    /** @var ParametreService */
    private $parametreService;

    /**
     * @return ParametreService
     */
    public function getParametreService()
    {
        return $this->parametreService;
    }

    /**
     * @param ParametreService $parametreService
     * @return ParametreService
     */
    public function setParametreService($parametreService)
    {
        $this->parametreService = $parametreService;
        return $this->parametreService;
    }


}