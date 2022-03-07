<?php

namespace StepStar\Service\Tef;

use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use StepStar\Service\Xml\XmlService;
use StepStar\Service\Xslt\XsltService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TefServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var array $config */
        $config = $container->get('Config');

        $xslTemplatePath = $config['step_star']['tef']['xml2tef_xsl_template_path'];

        /**
         * @var XsltService $xslService
         * @var XmlService $xmlService
         * @var TheseService $theseService
         */
        $xslService = $container->get(XsltService::class);
        $xmlService = $container->get(XmlService::class);
        $theseService = $container->get(TheseService::class);

        $service = new TefService();
        $service->setXmlService($xmlService);
        $service->setXsltService($xslService);
        $service->setXslTemplatePath($xslTemplatePath);
        $service->setTheseService($theseService);

        return $service;
    }
}