<?php

namespace Soutenance\Service\Url;

trait UrlServiceAwareTrait {

    private UrlService $urlService;

    /**
     * @return UrlService
     */
    public function getUrlService(): UrlService
    {
        return $this->urlService;
    }

    /**
     * @param UrlService $urlService
     */
    public function setUrlService(UrlService $urlService): void
    {
        $this->urlService = $urlService;
    }
}