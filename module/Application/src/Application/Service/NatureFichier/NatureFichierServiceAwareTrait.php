<?php

namespace Application\Service\NatureFichier;

trait NatureFichierServiceAwareTrait
{
    /**
     * @var NatureFichierService
     */
    protected $natureFichierService;

    /**
     * @param NatureFichierService $natureFichierService
     */
    public function setNatureFichierService(NatureFichierService $natureFichierService)
    {
        $this->natureFichierService = $natureFichierService;
    }
}