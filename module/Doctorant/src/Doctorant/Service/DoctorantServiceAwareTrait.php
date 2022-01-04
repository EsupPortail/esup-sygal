<?php

namespace Doctorant\Service;

trait DoctorantServiceAwareTrait
{
    /**
     * @var DoctorantService
     */
    protected $doctorantService;

    /**
     * @param DoctorantService $doctorantService
     */
    public function setDoctorantService(DoctorantService $doctorantService)
    {
        $this->doctorantService = $doctorantService;
    }
}