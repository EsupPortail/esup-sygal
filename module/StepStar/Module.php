<?php

namespace StepStar;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Console\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\Stdlib\Glob;
use StepStar\Controller\Envoi\EnvoiConsoleController;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
{
    // Nom du module
    const NAME = __NAMESPACE__;
    const STEP_STAR__CONSOLE_ROUTE__ENVOYER_THESES = 'step-star:envoyer-theses';

    public function getConfig()
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig(): array
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function getConsoleBanner(AdapterInterface $console): ?string
    {
        return "StepStar Module";
    }

    /**
     * @inheritDoc
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            /**
             * @see \StepStar\Controller\Oai\OaiConsoleController::generateConfigFileFromSiseOaiSetXmlFileAction()
             */
            'step-star:oai:generateConfigFileFromSiseOaiSetXmlFile' =>
                "Génère (remplace) le fichier config/others/sise_oai_set.config.php à partir du fichier data/oai/siseOaiSet.xml.",

            /**
             * @see \StepStar\Controller\Oai\OaiConsoleController::generateConfigFileFromOaiSetsXmlFileAction()
             */
            'step-star:oai:generateConfigFileFromOaiSetsXmlFile' =>
                "Génère (remplace) le fichier config/others/oai_sets.config.php à partir du fichier data/oai/oaiSets.xml.",

            /**
             * @see EnvoiConsoleController::envoyerThesesAction()
             */
            self::STEP_STAR__CONSOLE_ROUTE__ENVOYER_THESES . ' [--these=<id>] [--etat=<etat>] [--date-soutenance-min=<date-soutenance-min>] [--etablissement=<etablissement>] [--tag=<tag>] [--force]' =>
                "Pour chaque thèse spécifiée, génère le fichier XML intermédiaire puis le fichier TEF puis envoie ce dernier vers STEP/STAR.",
            [ '<id>', "Ids des thèses concernées, séparées par une virgule.", "Facultatif"],
            [ '<etat>', "États des thèses, séparés par une virgule, ex : 'E,S'.", "Facultatif"],
            [ '<date-soutenance-min>',
                "Si la valeur spécifiée est de la forme 'AAAA-MM-DD' : les thèses dont la date réelle de soutenance se situe avant cette date seront écartées, ex : '2022-03-11'. " .
                "Si elle est de la forme 'Nm' : les thèses dont la date réelle de soutenance est passée de plus de N mois seront écartées, ex : '6m'.",
                "Facultatif"],
            [ '<etablissement>', "Codes des établissements d'inscription, séparés par une virgule, ex : 'UCN,URN'.", "Facultatif"],
            [ '--force', "Réalise l'envoi même si le contenu du fichier TEF est le même qu'au dernier envoi.", "Facultatif"],
            [ '<tag>', "Tag éventuel permettant de retrouver facilement un ensemble de logs, ex : 'cron-2022-03-11'.", "Facultatif"],
        ];
    }
}
