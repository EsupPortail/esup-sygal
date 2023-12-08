<?php

namespace StepStar\Service\Xsl;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StepStar\Module;
use Webmozart\Assert\Assert;

class XslServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var array $config */
        $config = $container->get('Config');

        $xslTemplatePath = $config['step_star']['tef']['xsl_template_path'];
        $xslTemplateParams = $this->getXslTemplateParams($config['step_star']['tef']);

        $service = new XslService();
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
            'these',
//            'resultDocumentHref',
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