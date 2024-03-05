<?php

namespace Admission\Service\Etudiant;

trait EtudiantServiceAwareTrait
{
    /**
     * @var EtudiantService
     */
    protected EtudiantService $etudiantService;

    /**
     * @param EtudiantService $etudiantService
     */
    public function setEtudiantService(EtudiantService $etudiantService): void
    {
        $this->etudiantService = $etudiantService;
    }

    /**
     * @return EtudiantService
     */
    public function getEtudiantService(): EtudiantService
    {
        return $this->etudiantService;
    }
}