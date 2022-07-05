<?php

namespace Formation\Service\SessionStructureComplementaire;

trait SessionStructureComplementaireServiceAwareTrait {

    private SessionStructureComplementaireService $sessionStructureComplementaireService;

    /**
     * @return SessionStructureComplementaireService
     */
    public function getSessionStructureComplementaireService(): SessionStructureComplementaireService
    {
        return $this->sessionStructureComplementaireService;
    }

    /**
     * @param SessionStructureComplementaireService $sessionStructureComplementaireService
     */
    public function setSessionStructureComplementaireService(SessionStructureComplementaireService $sessionStructureComplementaireService): void
    {
        $this->sessionStructureComplementaireService = $sessionStructureComplementaireService;
    }

}