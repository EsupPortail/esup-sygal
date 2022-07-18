<?php

namespace Formation\Service\Formateur;

trait FormateurServiceAwareTrait
{
    private FormateurService $formateurService;

    /**
     * @return FormateurService
     */
    public function getFormateurService(): FormateurService
    {
        return $this->formateurService;
    }

    /**
     * @param FormateurService $formateurService
     */
    public function setFormateurService(FormateurService $formateurService): void
    {
        $this->formateurService = $formateurService;
    }

}