<?php

namespace Application\Service\Pays;

trait PaysServiceAwareTrait
{
    /**
     * @var PaysService
     */
    protected PaysService $paysService;

    /**
     * @param PaysService $paysService
     */
    public function setPaysService(PaysService $paysService)
    {
        $this->paysService = $paysService;
    }
}