<?php

namespace Application\Service\Parametre;

trait ParametreServiceAwareTrait
{
    /**
     * @var ParametreService
     */
    protected $parametreService;

    /**
     * @param ParametreService $parametreService
     */
    public function setParametreService(ParametreService $parametreService)
    {
        $this->parametreService = $parametreService;
    }
}