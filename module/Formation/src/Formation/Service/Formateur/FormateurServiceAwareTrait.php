<?php

namespace Formation\Service\Formateur;

trait FormateurServiceAwareTrait
{
    /** @var FormateurService */
    private $formateurService;

    /**
     * @return FormateurService
     */
    public function getFormateurService(): FormateurService
    {
        return $this->formateurService;
    }

    /**
     * @param FormateurService $formateurService
     * @return FormateurService
     */
    public function setFormateurService(FormateurService $formateurService): FormateurService
    {
        $this->formateurService = $formateurService;
        return $this->formateurService;
    }

}