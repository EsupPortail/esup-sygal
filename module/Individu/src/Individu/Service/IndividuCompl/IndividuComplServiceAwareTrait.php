<?php

namespace Individu\Service\IndividuCompl;

trait IndividuComplServiceAwareTrait
{
    private IndividuComplService $individuComplService;

    /**
     * @param IndividuComplService $individuComplService
     */
    public function setIndividuComplService(IndividuComplService $individuComplService): void
    {
        $this->individuComplService = $individuComplService;
    }
}