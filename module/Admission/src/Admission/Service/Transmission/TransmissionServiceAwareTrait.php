<?php

namespace Admission\Service\Transmission;

trait TransmissionServiceAwareTrait
{
    /**
     * @var TransmissionService
     */
    protected TransmissionService $transmissionService;

    /**
     * @param TransmissionService $transmissionService
     */
    public function setTransmissionService(TransmissionService $transmissionService): void
    {
        $this->transmissionService = $transmissionService;
    }

    /**
     * @return TransmissionService
     */
    public function getTransmissionService(): TransmissionService
    {
        return $this->transmissionService;
    }
}