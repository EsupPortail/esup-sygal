<?php

namespace Application\Service\ListeDiffusion;

trait ListeDiffusionServiceAwareTrait
{
    /**
     * @var ListeDiffusionService
     */
    protected $listeDiffusionService;

    /**
     * @param ListeDiffusionService $listeDiffusionService
     * @return self
     */
    public function setListeDiffusionService(ListeDiffusionService $listeDiffusionService)
    {
        $this->listeDiffusionService = $listeDiffusionService;

        return $this;
    }
}