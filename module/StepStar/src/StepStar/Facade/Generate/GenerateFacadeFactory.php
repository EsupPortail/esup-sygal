<?php

namespace StepStar\Facade\Generate;

use Psr\Container\ContainerInterface;
use StepStar\Service\Log\LogService;
use StepStar\Service\Tef\TefService;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xsl\XslService;
use StepStar\Service\Zip\ZipService;

class GenerateFacadeFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GenerateFacade
    {
        $facade = new GenerateFacade();

        /** @var \StepStar\Service\Xml\XmlService $xmlService */
        $xmlService = $container->get(XmlService::class);
        $facade->setXmlService($xmlService);

        /** @var \StepStar\Service\Xsl\XslService $xslService */
        $xslService = $container->get(XslService::class);
        $facade->setXslService($xslService);

        /** @var \StepStar\Service\Tef\TefService $tefService */
        $tefService = $container->get(TefService::class);
        $facade->setTefService($tefService);

        /** @var \StepStar\Service\Log\LogService $logService */
        $logService = $container->get(LogService::class);
        $facade->setLogService($logService);

        /** @var \StepStar\Service\Zip\ZipService $zipService */
        $zipService = $container->get(ZipService::class);
        $facade->setZipService($zipService);

        /** @var array $config */
        $config = $container->get('Config');
        $outputDirPathPrefix = $config['step_star']['tef']['output_dir_path_prefix'] ?? (sys_get_temp_dir() . '/sygal_stepstar_');
        $facade->setOutputDirPathPrefix($outputDirPathPrefix);

        return $facade;
    }
}