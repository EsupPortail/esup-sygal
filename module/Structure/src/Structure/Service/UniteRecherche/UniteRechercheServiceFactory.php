<?php

namespace Structure\Service\UniteRecherche;

use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class UniteRechercheServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UniteRechercheService
    {
        $service = new UniteRechercheService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
