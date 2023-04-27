<?php
namespace Formation;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Stdlib\Glob;

class Module
{
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
            'formation:session:terminer-auto' =>
                'Terminaison des sessions dont toutes les séances sont passées',
//            ['--these',           "Id de la thèse concernée"],
//            ['--versionFichier',  "Version du fichier de thèse à utiliser (ex: 'VA', 'VOC')"],
//            ['--removeFirstPage', "(facultatif) Témoin indiquant si la première page doit être retirée avant la fusion"],
//            ['--notifier',        "(facultatif) Adresses électroniques auxquelles envoyer un courriel une fois la fusion terminée"],
        ];
    }
}
