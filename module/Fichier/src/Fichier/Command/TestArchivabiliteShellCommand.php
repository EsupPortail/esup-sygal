<?php

namespace Fichier\Command;

use Application\Command\ShellCommand;
use Application\Command\ShellCommandResult;
use Webmozart\Assert\Assert;

class TestArchivabiliteShellCommand extends ShellCommand
{
    /**
     * @var string Chemin absolu du script à exécuter.
     */
    protected string $scriptPath;

    protected ?string $url = null;

    protected ?int $maxExecutionTime = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'TestArchivabiliteShellCommand';
    }

    /**
     * Constructeur.
     *
     * @param string $scriptPath Chemin absolu du script à exécuter.
     * @param array $options
     */
    public function __construct(string $scriptPath, array $options = [])
    {
        $this->scriptPath = $scriptPath;
        $this->options = $options;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function setMaxExecutionTime(?int $maxExecutionTime): self
    {
        $this->maxExecutionTime = $maxExecutionTime;
        return $this;
    }

    public function generateCommandLine(): void
    {
        $command = sprintf('%s %s --file "%s" %s %s',
            $this->generateEnvVarsString(),
            realpath($this->scriptPath),
            $this->inputFilePath,
            $this->url ? sprintf('--url "%s"', $this->url) : '',
            $this->maxExecutionTime ? sprintf('--maxtime %d', $this->maxExecutionTime) : '');

        $this->commandLine = $command;
    }

    private function generateEnvVarsString(): string
    {
        $envVars = [];

        if ($proxyParams = $this->options['proxy'] ?? []) {
            Assert::keyExists($proxyParams, 'enabled');
            $proxyEnabled = (bool) $proxyParams['enabled'];
            if ($proxyEnabled) {
                Assert::keyExists($proxyParams, 'proxy_host');
                Assert::keyExists($proxyParams, 'proxy_port');
                $envVars['http_proxy'] = $proxyParams['proxy_host'] . ':' . $proxyParams['proxy_port'];
                $envVars['https_proxy'] = '$http_proxy';
            }
        }

        array_walk($envVars, function(&$v, $k) {
            $v = $k . '=' . $v;
        });

        return implode(' ', $envVars);
    }

    public function createResult(array $output, int $returnCode): ShellCommandResult
    {
        return new TestArchivabiliteShellCommandResult($output, $returnCode);
    }
}