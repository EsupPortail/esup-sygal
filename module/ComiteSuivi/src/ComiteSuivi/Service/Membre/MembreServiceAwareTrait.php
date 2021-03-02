<?php

namespace ComiteSuivi\Service\Membre;

trait MembreServiceAwareTrait {

    /** @var MembreService */
    private $membreService;

    /**
     * @return MembreService
     */
    public function getMembreService() : MembreService
    {
        return $this->membreService;
    }

    /**
     * @param MembreService $membreService
     * @return MembreService
     */
    public function setMembreService(MembreService $membreService) : MembreService
    {
        $this->membreService = $membreService;
        return $this->membreService;
    }

}