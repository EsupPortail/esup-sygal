<?php

namespace UnicaenDeploy\Domain;

class Target
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $php_version = "7.3";

    /**
     * @var string
     */
    private $branch = 'master';

    /**
     * @var TaskInterface[]
     */
    private $tasks = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Target
     */
    public function setName(string $name): Target
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhpVersion(): string
    {
        return $this->php_version;
    }

    /**
     * @param string $php_version
     * @return Target
     */
    public function setPhpVersion(string $php_version): Target
    {
        $this->php_version = $php_version;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }

    /**
     * @param string $branch
     * @return Target
     */
    public function setBranch(string $branch): Target
    {
        $this->branch = $branch;
        return $this;
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @param TaskInterface $task
     * @return Target
     */
    public function addTask(TaskInterface $task): Target
    {
        $this->tasks[] = $task;
        return $this;
    }
}