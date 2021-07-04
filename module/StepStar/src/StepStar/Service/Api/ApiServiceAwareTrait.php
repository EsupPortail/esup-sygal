<?php

namespace StepStar\Service\Api;

trait ApiServiceAwareTrait
{
    /**
     * @var ApiService
     */
    protected $apiService;

    /**
     * @param ApiService $apiService
     * @return self
     */
    public function setApiService(ApiService $apiService): self
    {
        $this->apiService = $apiService;
        return $this;
    }


}