<?php

namespace StepStar;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Stdlib\Glob;
use StepStar\Controller\Envoi\EnvoiConsoleController;

class Module
{
    // Nom du module
    const NAME = __NAMESPACE__;
    const STEP_STAR__CONSOLE_ROUTE__ENVOYER_FICHIERS = 'step-star:envoyer-fichiers';
    const STEP_STAR__CONSOLE_ROUTE__ENVOYER_THESES = 'step-star:envoyer-theses';
    const STEP_STAR__CONSOLE_ROUTE__GENERER_THESES = 'step-star:generer-theses';

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

    public function getConsoleUsage(): array
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
             * @see \StepStar\Controller\Generate\GenerateConsoleController::generateFacade()
             */
            self::STEP_STAR__CONSOLE_ROUTE__GENERER_THESES . ' 
    [--these <id>] 
    [--etat <etat>] 
    [--etablissement <etablissement>] 
    [--date-soutenance-null] 
    [--date-soutenance-min <date-soutenance-min>] 
    [--date-soutenance-max <date-soutenance-max>]' => "Pour chaque thèse spécifiée, génère le fichier XML intermédiaire puis le fichier TEF.",
            [ '--these <id>', "Ids des thèses concernées, séparées par une virgule. Facultatif"],
            [ '--etat <etat>', "États des thèses, séparés par une virgule, ex : 'E,S'. Facultatif"],
            [ '--etablissement <etablissement>', "Codes des établissements d'inscription, séparés par une virgule, ex : 'UCN,URN'. Facultatif"],
            [ '--date-soutenance-null', "Ne retient que les thèses dont la date de soutenance est non renseignée. Facultatif"],
            [ '--date-soutenance-min <date-soutenance-min>',
                "Date de soutenance minimale. " .
                "Si la valeur spécifiée est de la forme 'AAAA-MM-DD' : les thèses dont la date réelle de soutenance se situe AVANT cette date seront écartées, ex : '2022-03-11'. " .
                "Si la valeur commence par 'P' : un DateInterval sera construit et retranché à la date du jour pour déterminer la date de soutenance minimale, ex : 'P6M' <=> '6 mois AVANT la date du jour'. " .
                "Facultatif"],
            [ '--date-soutenance-max <date-soutenance-max>',
                "Date de soutenance maximale. " .
                "Si la valeur spécifiée est de la forme 'AAAA-MM-DD' : les thèses dont la date réelle de soutenance se situe APRÈS cette date seront écartées, ex : '2022-07-09'. " .
                "Si la valeur commence par 'P' : un DateInterval sera construit et additionné à la date du jour pour déterminer la date de soutenance maximale, ex : 'P6M' <=> '6 mois APRÈS la date du jour'. " .
                "Facultatif"],

            /**
             * @see EnvoiConsoleController::envoyerFichiersAction()
             */
            self::STEP_STAR__CONSOLE_ROUTE__ENVOYER_FICHIERS . ' --dir <dir> [--tag <tag>]' =>
                "Envoie vers STEP/STAR les fichiers TEF présents dans le répertoire spécifié.",
            [ '--dir <dir>', "Chemin absolu du répertoire contenant les fichiers TEF. Obligatoire"],
            [ '--tag <tag>', "Tag éventuel permettant de retrouver facilement un ensemble de logs, ex : 'cron-2022-03-11'. Facultatif"],

            /**
             * @see EnvoiConsoleController::envoyerThesesAction()
             */
            self::STEP_STAR__CONSOLE_ROUTE__ENVOYER_THESES . ' 
    [--these <id>] 
    [--etat <etat>] 
    [--etablissement <etablissement>] 
    [--date-soutenance-null] 
    [--date-soutenance-min <date-soutenance-min>] 
    [--date-soutenance-max <date-soutenance-max>] 
    [--tag <tag>] 
    [--force]
    [--clean]' => "Envoie vers STEP/STAR les thèses spécifiées l'une après l'autre (uniquement si leur TEF a changé depuis le dernier envoi).",
            [ '--these <id>', "Ids des thèses concernées, séparées par une virgule. Facultatif"],
            [ '--etat <etat>', "États des thèses, séparés par une virgule, ex : 'E,S'. Facultatif"],
            [ '--etablissement <etablissement>', "Codes des établissements d'inscription, séparés par une virgule, ex : 'UCN,URN'. Facultatif"],
            [ '--date-soutenance-null', "Ne retient que les thèses dont la date de soutenance est non renseignée. Facultatif"],
            [ '--date-soutenance-min <date-soutenance-min>',
                "Date de soutenance minimale. " .
                "Si la valeur spécifiée est de la forme 'AAAA-MM-DD' : les thèses dont la date réelle de soutenance se situe AVANT cette date seront écartées, ex : '2022-03-11'. " .
                "Si la valeur commence par 'P' : un DateInterval sera construit et retranché à la date du jour pour déterminer la date de soutenance minimale, ex : 'P6M' <=> '6 mois AVANT la date du jour'. " .
                "Facultatif"],
            [ '--date-soutenance-max <date-soutenance-max>',
                "Date de soutenance maximale. " .
                "Si la valeur spécifiée est de la forme 'AAAA-MM-DD' : les thèses dont la date réelle de soutenance se situe APRÈS cette date seront écartées, ex : '2022-07-09'. " .
                "Si la valeur commence par 'P' : un DateInterval sera construit et additionné à la date du jour pour déterminer la date de soutenance maximale, ex : 'P6M' <=> '6 mois APRÈS la date du jour'. " .
                "Facultatif"],
            [ '--tag <tag>', "Tag éventuel permettant de retrouver facilement un ensemble de logs, ex : 'cron-2022-03-11'. Facultatif"],
            [ '--force', "Réalise l'envoi même si le contenu du fichier TEF est le même qu'au dernier envoi. Facultatif"],
            [ '--clean', "Une fois l'envoi effectué, supprimer les fichiers XML temporaires générés. Facultatif"],
        ];
    }
}
