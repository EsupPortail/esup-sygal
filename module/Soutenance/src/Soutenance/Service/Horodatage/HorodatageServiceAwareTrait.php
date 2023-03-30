<?php

namespace Soutenance\Service\Horodatage;

trait HorodatageServiceAwareTrait {

    private HorodatageService $horodatageService;

    /**
     * @return HorodatageService
     */
    public function getHorodatageService(): HorodatageService
    {
        return $this->horodatageService;
    }

    /**
     * @param HorodatageService $horodatageService
     */
    public function setHorodatageService(HorodatageService $horodatageService): void
    {
        $this->horodatageService = $horodatageService;
    }


}