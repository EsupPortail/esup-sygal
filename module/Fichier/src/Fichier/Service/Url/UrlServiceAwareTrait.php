<?php

namespace Fichier\Service\Url;

trait UrlServiceAwareTrait
{
    protected UrlService $urlFichierService;

    public function setUrlFichierService(UrlService $urlFichierService): void
    {
        $this->urlFichierService = $urlFichierService;
    }
}