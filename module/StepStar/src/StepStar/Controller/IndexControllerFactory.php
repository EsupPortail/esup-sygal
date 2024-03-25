<?php

namespace StepStar\Controller;

use Psr\Container\ContainerInterface;
use StepStar\Service\Log\LogService;

class IndexControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndexController
    {
        $controller = new IndexController();

        /** @var \StepStar\Service\Log\LogService $logService */
        $logService = $container->get(LogService::class);
        $controller->setLogService($logService);

        $config = $container->get('Config')['step_star'];
        $infos = [
            'Web Service destination' => $config['api']['soap_client']['wsdl']['url'],
            'Id établissement' => $config['api']['params']['idEtablissement'],
            "Autorité SUDOC étab soutenance" => $config['tef']['xsl_template_params']['autoriteSudoc_etabSoutenance'],
            "Répertoire de travail pour la génération des fichiers TEF" => $config['tef']['output_dir_path_prefix'],
            "Suppression des fichiers générés après l'envoi des thèses" => $config['tef']['clean_after_work'] ? 'Oui' : 'Non',
        ];
        $controller->setInfos($infos);

        return $controller;
    }
}