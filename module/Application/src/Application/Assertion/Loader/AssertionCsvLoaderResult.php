<?php

namespace Application\Assertion\Loader;

class AssertionCsvLoaderResult
{
    /**
     * @var string
     */
    private $ruleFilePath;

    /**
     * @var string
     */
    private $assertionClass;

    /**
     * @var string[]
     */
    private $testNames;

    /**
     * @var array[]
     */
    private $data;

    /**
     * @param string $ruleFilePath
     * @return self
     */
    public function setRuleFilePath($ruleFilePath)
    {
        $this->ruleFilePath = $ruleFilePath;

        return $this;
    }

    /**
     * @param string $assertionClass
     * @return self
     */
    public function setAssertionClass($assertionClass)
    {
        $this->assertionClass = $assertionClass;

        return $this;
    }

    /**
     * @param string[] $testNames
     * @return self
     */
    public function setTestNames($testNames)
    {
        $this->testNames = $testNames;

        return $this;
    }

    /**
     * @param array[] $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getRuleFilePath()
    {
        return $this->ruleFilePath;
    }

    /**
     * @return string
     */
    public function getAssertionClass()
    {
        return $this->assertionClass;
    }

    /**
     * @return string[]
     */
    public function getTestNames()
    {
        return $this->testNames;
    }

    /**
     * @return array[]
     */
    public function getData()
    {
        return $this->data;
    }
}