<?php

namespace Application\Service\Doctorant;

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