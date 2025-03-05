<?php

namespace  Soutenance\Service\Membre;

trait MembreServiceAwareTrait {

    /** @var MembreService */
    protected $membreService;

    /**
     * @return MembreService
     */
    public function getMembreService()
    {
        return $this->membreService;
    }

    /**
     * @param MembreService $membreService
     * @return MembreService
     */
    public function setMembreService($membreService)
    {
        $this->membreService = $membreService;
        return $this->membreService;
    }

}