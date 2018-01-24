<?php

namespace Application\Service\Individu;

trait IndividuServiceAwareTrait
{
    /**
     * @var IndividuService
     */
    protected $individuService;

    /**
     * @param IndividuService $individuService
     */
    public function setIndividuService(IndividuService $individuService)
    {
        $this->individuService = $individuService;
    }
}