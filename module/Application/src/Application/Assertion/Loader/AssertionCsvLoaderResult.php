<?php

namespace Application\Assertion\Loader;

class AssertionCsvLoaderResult
{
    private string $ruleFilePath;
    private string $assertionClass;

    /**
     * @var string[]
     */
    private array $uses;

    /**
     * @var string[]
     */
    private array $testNames;

    /**
     * @var array[]
     */
    private array $data;

    public function setRuleFilePath(string $ruleFilePath): self
    {
        $this->ruleFilePath = $ruleFilePath;

        return $this;
    }

    public function setAssertionClass(string $assertionClass): self
    {
        $this->assertionClass = $assertionClass;

        return $this;
    }

    public function setUses(array $uses): self
    {
        $this->uses = $uses;

        return $this;
    }

    public function setTestNames(array $testNames): self
    {
        $this->testNames = $testNames;

        return $this;
    }

    /**
     * @param array[] $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getRuleFilePath(): string
    {
        return $this->ruleFilePath;
    }

    public function getAssertionClass(): string
    {
        return $this->assertionClass;
    }

    /**
     * @return string[]
     */
    public function getUses(): array
    {
        return $this->uses;
    }

    /**
     * @return string[]
     */
    public function getTestNames(): array
    {
        return $this->testNames;
    }

    /**
     * @return array[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}