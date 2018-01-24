<?php

namespace Application\Service\EcoleDoctorale;

trait EcoleDoctoraleServiceAwareTrait
{
    /**
     * @var EcoleDoctoraleService
     */
    protected $ecoleDoctoraleService;

    /**
     * @param EcoleDoctoraleService $ecoleDoctoraleService
     */
    public function setEcoleDoctoraleService(EcoleDoctoraleService $ecoleDoctoraleService)
    {
        $this->ecoleDoctoraleService = $ecoleDoctoraleService;
    }
}