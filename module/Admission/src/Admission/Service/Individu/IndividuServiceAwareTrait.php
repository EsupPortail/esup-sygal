<?php

namespace Admission\Service\Individu;

trait IndividuServiceAwareTrait
{
    /**
     * @var IndividuService
     */
    protected IndividuService $individuAdmissionService;

    /**
     * @param IndividuService $individuAdmissionService
     */
    public function setIndividuAdmissionService(IndividuService $individuAdmissionService): void
    {
        $this->individuAdmissionService = $individuAdmissionService;
    }

    /**
     * @return IndividuService
     */
    public function getIndividuAdmissionService(): IndividuService
    {
        return $this->individuAdmissionService;
    }
}