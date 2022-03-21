<?php

namespace StepStar\Service\Tef;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use StepStar\Module;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xslt\XsltService;
use Webmozart\Assert\Assert;

class TefServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TefService
    {
        /** @var array $config */
        $config = $container->get('Config');

        $xslTemplatePath = $config['step_star']['tef']['xsl_template_path'];
        $xslTemplateParams = $this->getXslTemplateParams($config['step_star']['tef']);

        /**
         * @var \StepStar\Service\Xslt\XsltService $xslService
         * @var \StepStar\Service\Xml\XmlService $xmlService
         */
        try {
            $xslService = $container->get(XsltService::class);
        } catch (ContainerExceptionInterface $e) {
        }
        $xmlService = $container->get(XmlService::class);

        $service = new TefService();
        $service->setXmlService($xmlService);
        $service->setXsltService($xslService);
        $service->setXslTemplatePath($xslTemplatePath);
        $service->setXslTemplateParams($xslTemplateParams);

        return $service;
    }

    private function getXslTemplateParams(array $tefConfig): array
    {
        $xslTemplateParams = $tefConfig['xsl_template_params'];

        $keys = [
            'etablissementStepStar',
            'autoriteSudoc_etabSoutenance',
            'thesesRootTag',
            'theseTag',
            'resultDocumentHref',
        ];

        foreach ($keys as $k) {
            Assert::keyExists(
                $xslTemplateParams,
                $k,
                "Clé '$k' introuvable dans la config 'tef' du module " . Module::NAME);
        }

        return $xslTemplateParams;
    }
}