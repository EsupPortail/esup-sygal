<?php

namespace StepStar\Service\Xml;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use XMLWriter;

class XmlServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): XmlService
    {
        /** @var array $config */
        $config = $container->get('Config');

        $codesSiseDisciplinesToCodesDomaines = $config['step_star']['oai']['sise_oai_set'];
        $codesTypeFinancContratDoctoral = $config['step_star']['xml']['codes_type_financ_contrat_doctoral'];
        $codesOrigFinancCifre = $config['step_star']['xml']['codes_orig_financ_cifre'];
        $paramsPartenaireRecherche = $config['step_star']['xml']['params_partenaire_recherche'];

        $service = new XmlService();
        $service->setWriter(new XMLWriter());
        $service->setCodesSiseDisciplinesToCodesDomaines($codesSiseDisciplinesToCodesDomaines);
        $service->setCodesTypeFinancContratDoctoral($codesTypeFinancContratDoctoral);
        $service->setCodesOrigFinancCifre($codesOrigFinancCifre);
        $service->setParamsPartenaireRecherche($paramsPartenaireRecherche);

        return $service;
    }
}