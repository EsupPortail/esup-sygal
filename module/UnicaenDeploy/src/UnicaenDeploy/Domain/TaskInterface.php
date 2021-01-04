<?php

namespace UnicaenDeploy\Domain;

interface TaskInterface
{
    /**
     * @return Host
     */
    public function getHost();

    /**
     * @param Host $host
     * @return Task
     */
    public function setHost(Host $host);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param array $config
     * @return Task
     */
    public function setConfig(array $config);

    /**
     * @param array $args
     * @param string $binDir
     * @return mixed
     */
    public function run(array $args, string $binDir);
}