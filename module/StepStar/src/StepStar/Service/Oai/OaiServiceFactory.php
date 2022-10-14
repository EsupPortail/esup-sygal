<?php

namespace StepStar\Service\Oai;

use Psr\Container\ContainerInterface;
use StepStar\Module;
use Webmozart\Assert\Assert;

class OaiServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): OaiService
    {
        /** @var array $config */
        $config = $container->get('Config');
        $moduleConfig = $config['step_star'];
        Assert::keyExists($moduleConfig, $oai = 'oai', "Clé '$oai' introuvable dans la config du module " . Module::NAME);
        $oaiConfig = $moduleConfig['oai'];
        Assert::keyExists($oaiConfig, $k = 'sise_oai_set', "Clé '$k' introuvable dans la config '$oai'");
        Assert::keyExists($oaiConfig, $k = 'oai_sets', "Clé '$k' introuvable dans la config '$oai'");

        $service = new OaiService();
        $service->setSiseOaiSetXmlFilePath($oaiConfig['sise_oai_set_file_path']);
        $service->setOaiSetsXmlFilePath($oaiConfig['oai_sets_file_path']);

        return $service;
    }
}