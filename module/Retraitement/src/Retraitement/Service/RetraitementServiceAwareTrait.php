<?php

namespace Retraitement\Service;

trait RetraitementServiceAwareTrait
{
    /**
     * @var RetraitementService
     */
    protected $retraitementService;

    /**
     * @param RetraitementService $service
     */
    public function setRetraitementService(RetraitementService $service)
    {
        $this->retraitementService = $service;
    }
}