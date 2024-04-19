<?php

namespace Application\Service\ListeDiffusion\Url;

trait UrlServiceAwareTrait
{
    protected UrlService $urlService;

    public function setUrlService(UrlService $urlService): void
    {
        $this->urlService = $urlService;
    }
}