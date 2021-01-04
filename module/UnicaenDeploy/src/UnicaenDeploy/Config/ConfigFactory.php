<?php

namespace UnicaenDeploy\Config;

use Interop\Container\ContainerInterface;
use UnicaenDeploy\Domain\Host;
use UnicaenDeploy\Domain\Target;
use UnicaenDeploy\Domain\TaskInterface;
use Webmozart\Assert\Assert;
use Zend\ServiceManager\Factory\FactoryInterface;

class ConfigFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var array $config */
        $appconfig = $container->get('Config');

        Assert::keyExists($appconfig, $key = 'unicaen-deploy', "Clé '$key' introuvable dans la config de l'appli");
        $deployConfig = $appconfig[$key];

        Assert::keyExists($deployConfig, 'hosts');

        $config = new Config();

        // hosts
        $hostsConfig = $deployConfig['hosts'] ?? [];
        $hosts = [];
        foreach ($hostsConfig as $hostConfig) {
            Assert::keyExists($hostConfig, 'name');
            Assert::keyExists($hostConfig, 'ssh_hostname');
            Assert::keyExists($hostConfig, 'ssh_username');
            Assert::keyExists($hostConfig, 'barerepodir');

            $host = new Host();
            $host->setName($hostConfig['name']);
            $host->setSshHostname($hostConfig['ssh_hostname']);
            $host->setSshUsername($hostConfig['ssh_username']);
            $host->setSshPubkeyPath($hostConfig['ssh_pubkey_path'] ?? null);
            $host->setSshPrivkeyPath($hostConfig['ssh_privkey_path'] ?? null);
            $host->setSshTunnelHostname($hostConfig['ssh_tunnel_hostname'] ?? null);
            $host->setSshTunnelUsername($hostConfig['ssh_tunnel_username'] ?? null);
            $host->setBarerepodir($hostConfig['barerepodir']);
            $host->setAppdir($hostConfig['appdir'] ?? '/var/www/html');

            $config->addHost($host);

            $hosts[$host->getName()] = $host;
        }

        // targets
        $targetsConfig = $deployConfig['targets'] ?? [];
        foreach ($targetsConfig as $targetConfig) {
            Assert::keyExists($targetConfig, 'name');
            Assert::keyExists($targetConfig, 'php_version');

            $target = new Target();
            $target->setName($targetConfig['name']);
            $target->setPhpVersion($targetConfig['php_version']);
            $target->setBranch($targetConfig['branch'] ?? 'master');

            $tasksConfig = $targetConfig['tasks'] ?? [];
            foreach ($tasksConfig as $taskConfig) {
                Assert::keyExists($taskConfig, 'type');
                Assert::keyExists($taskConfig, 'host');
                Assert::keyExists($taskConfig, 'config');

                /** @var TaskInterface $task */
                $task = $container->get($taskConfig['type']);
                Assert::implementsInterface($task, TaskInterface::class, "Le service spécifié pour la clé 'type' doit implémenter " . TaskInterface::class);
                $task->setHost($hosts[$taskConfig['host']]);
                $task->setConfig($taskConfig['config']);

                $target->addTask($task);
            }

            $config->addTarget($target);
        }

        return $config;
    }
}