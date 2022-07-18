<?php

namespace Formation\Service\SessionStructureValide;

trait SessionStructureValideServiceAwareTrait {

    private SessionStructureValideService $sessionStructureComplementaireService;

    /**
     * @return SessionStructureValideService
     */
    public function getSessionStructureValideService(): SessionStructureValideService
    {
        return $this->sessionStructureComplementaireService;
    }

    /**
     * @param SessionStructureValideService $sessionStructureComplementaireService
     */
    public function setSessionStructureValideService(SessionStructureValideService $sessionStructureComplementaireService): void
    {
        $this->sessionStructureComplementaireService = $sessionStructureComplementaireService;
    }

}