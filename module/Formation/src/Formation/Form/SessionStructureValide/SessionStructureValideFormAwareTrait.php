<?php

namespace Formation\Form\SessionStructureValide;

trait SessionStructureValideFormAwareTrait {

    private SessionStructureValideForm $sessionStructureComplementaireForm;

    /**
     * @return SessionStructureValideForm
     */
    public function getSessionStructureValideForm(): SessionStructureValideForm
    {
        return $this->sessionStructureComplementaireForm;
    }

    /**
     * @param SessionStructureValideForm $sessionStructureComplementaireForm
     */
    public function setSessionStructureValideForm(SessionStructureValideForm $sessionStructureComplementaireForm): void
    {
        $this->sessionStructureComplementaireForm = $sessionStructureComplementaireForm;
    }
}