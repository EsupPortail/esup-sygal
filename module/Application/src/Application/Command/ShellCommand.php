<?php

namespace Application\Command;

abstract class ShellCommand implements ShellCommandInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $commandLine;

    /**
     * @var string
     */
    protected $outputFilePath;

    /**
     * @var string
     */
    protected $inputFilePath;

    /**
     * @var string
     */
    protected $errorFilePath;

    /**
     * @param string $outputFilePath
     * @return self
     */
    public function setOutputFilePath(string $outputFilePath): self
    {
        $this->outputFilePath = $outputFilePath;
        return $this;
    }

    /**
     * @param string $inputFilePath
     * @return self
     */
    public function setInputFilePath(string $inputFilePath): self
    {
        $this->inputFilePath = $inputFilePath;
        return $this;
    }

    /**
     * @param string $errorFilePath
     * @return self
     */
    public function setErrorFilePath(string $errorFilePath): self
    {
        $this->errorFilePath = $errorFilePath;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function checkRequirements()
    {

    }

    /**
     * @inheritDoc
     */
    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    /**
     * @inheritDoc
     */
    public function createResult(array $output, int $returnCode): ShellCommandResult
    {
        return new ShellCommandResult($output, $returnCode);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}