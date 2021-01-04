<?php

namespace UnicaenDeploy\Config;

use UnicaenDeploy\Domain\Host;
use UnicaenDeploy\Domain\Target;

class Config
{
    /**
     * @var string
     */
    private $binDir;

    /**
     * @var string
     */
    private $repo = "git@git.unicaen.fr:dsi/sygal.git";

    /**
     * @var string
     */
    private $tmpdir = "/tmp/sygal-deploiement";

    /**
     * @var Host[]
     */
    private $hosts = [];

    /**
     * @var Target[]
     */
    private $targets = [];

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->binDir = realpath(__DIR__ . '/../../../bin');
        $this->tmpdir = tempnam(sys_get_temp_dir(), 'unicaen-deploy_');
    }

    /**
     * @return string
     */
    public function getBinDir(): string
    {
        return $this->binDir;
    }

    /**
     * @return string
     */
    public function getRepo(): string
    {
        return $this->repo;
    }

    /**
     * @param string $repo
     * @return Config
     */
    public function setRepo(string $repo): Config
    {
        $this->repo = $repo;
        return $this;
    }

    /**
     * @return string
     */
    public function getTmpdir(): string
    {
        return $this->tmpdir;
    }

    /**
     * @param string $tmpdir
     * @return Config
     */
    public function setTmpdir(string $tmpdir): Config
    {
        $this->tmpdir = $tmpdir;
        return $this;
    }

    /**
     * @return Host[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * @param Host $host
     * @return Config
     */
    public function addHost(Host $host): Config
    {
        $this->hosts[] = $host;
        return $this;
    }

    /**
     * @return Target[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * @param Target $target
     * @return Config
     */
    public function addTarget(Target $target): Config
    {
        $this->targets[] = $target;
        return $this;
    }
}