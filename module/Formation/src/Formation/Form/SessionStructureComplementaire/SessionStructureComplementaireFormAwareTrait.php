<?php

namespace Formation\Form\SessionStructureComplementaire;

trait SessionStructureComplementaireFormAwareTrait {

    private SessionStructureComplementaireForm $sessionStructureComplementaireForm;

    /**
     * @return SessionStructureComplementaireForm
     */
    public function getSessionStructureComplementaireForm(): SessionStructureComplementaireForm
    {
        return $this->sessionStructureComplementaireForm;
    }

    /**
     * @param SessionStructureComplementaireForm $sessionStructureComplementaireForm
     */
    public function setSessionStructureComplementaireForm(SessionStructureComplementaireForm $sessionStructureComplementaireForm): void
    {
        $this->sessionStructureComplementaireForm = $sessionStructureComplementaireForm;
    }
}