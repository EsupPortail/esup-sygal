<?php

namespace Application\Service\Etablissement;

use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class EtablissementServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EtablissementService
     */
    public function __invoke(ContainerInterface $container)
    {
        $service = new EtablissementService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
