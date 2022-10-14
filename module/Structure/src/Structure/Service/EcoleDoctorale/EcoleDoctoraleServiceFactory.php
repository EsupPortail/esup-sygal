<?php

namespace Structure\Service\EcoleDoctorale;

use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class EcoleDoctoraleServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EcoleDoctoraleService
    {
        $service = new EcoleDoctoraleService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
