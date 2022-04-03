<?php

namespace Application\Service\IndividuCompl;

trait IndividuComplServiceAwareTrait {

    /** @var IndividuComplService */
    private $individuComplService;

    /**
     * @return IndividuComplService
     */
    public function getIndividuComplService(): IndividuComplService
    {
        return $this->individuComplService;
    }

    /**
     * @param IndividuComplService $individuComplService
     */
    public function setIndividuComplService(IndividuComplService $individuComplService): void
    {
        $this->individuComplService = $individuComplService;
    }
}