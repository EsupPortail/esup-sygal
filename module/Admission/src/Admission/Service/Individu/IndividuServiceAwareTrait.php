<?php

namespace Admission\Service\Individu;

trait IndividuServiceAwareTrait
{
    /**
     * @var IndividuService
     */
    protected IndividuService $individuService;

    /**
     * @param IndividuService $individuService
     */
    public function setIndividuService(IndividuService $individuService): void
    {
        $this->individuService = $individuService;
    }

    /**
     * @return IndividuService
     */
    public function getIndividuService(): IndividuService
    {
        return $this->individuService;
    }
}