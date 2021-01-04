<?php

namespace UnicaenDeploy\Service;

trait DeployServiceAwareTrait
{
    /**
     * @var DeployService
     */
    protected $deployService;

    /**
     * @param DeployService $deployService
     * @return self
     */
    public function setDeployService(DeployService $deployService): self
    {
        $this->deployService = $deployService;
        return $this;
    }
}