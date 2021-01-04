<?php

namespace UnicaenDeploy\Service;

use UnicaenApp\Exception\RuntimeException;
use UnicaenDeploy\Config\Config;
use UnicaenDeploy\Domain\Target;
use UnicaenDeploy\Domain\TaskInterface;

class DeployService
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     * @return DeployService
     */
    public function setConfig(Config $config): DeployService
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $targetName
     */
    public function processTargetByName(string $targetName)
    {
        $target = null;
        foreach ($this->config->getTargets() as $t) {
            if ($t->getName() === $targetName) {
                $target = $t;
            }
        }
        if ($target === null) {
            throw new RuntimeException("Aucune target trouvée avec ce nom : " . $targetName);
        }

        $this->processTarget($target);
    }

    /**
     * @param Target $target
     */
    public function processTarget(Target $target)
    {
        foreach ($target->getTasks() as $task) {
            $host = $task->getHost();

            $vars = [];
            $vars['TMPDIR'] = $this->config->getTmpdir();
            $vars['PHP_VERSION'] = $target->getPhpVersion();
            $vars['BRANCH'] = $target->getBranch();
            $vars['DESTINATION'] = $host->getSshUsername() . '@' . $host->getSshHostname();
            $vars['HOSTNAME'] = $host->getSshHostname();
            $vars['USERNAME'] = $host->getSshUsername();
            $vars['APPDIR'] = $host->getAppdir();
            $vars['BAREREPODIR'] = $host->getBarerepodir();

            $this->runTask($task, $vars);
        }
    }

    /**
     * @param TaskInterface $task
     * @param string[] $args
     */
    private function runTask(TaskInterface $task, array $args = [])
    {
        $result = $task->run($args, $this->config->getBinDir());
        var_dump(
            $result
        );
    }
}