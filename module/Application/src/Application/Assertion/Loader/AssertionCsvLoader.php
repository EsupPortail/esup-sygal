<?php

namespace Application\Assertion\Loader;

use Application\Assertion\Loader\AssertionCsvLoaderResult;
use UnicaenApp\Exception\LogicException;

class AssertionCsvLoader
{
    const SEPARATOR = ';';
    const COLUMN_FOR_LINE = 0;
    const COLUMN_FOR_ENABLED = 1;
    const COLUMN_FOR_PRIVILEGE = 2;

    /**
     * @var string
     */
    private $ruleFilePath;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var AssertionCsvLoaderResult
     */
    private $result;

    /**
     * @param string $ruleFilePath
     * @return self
     */
    public function setRuleFilePath($ruleFilePath)
    {
        $this->ruleFilePath = $ruleFilePath;
        $this->loaded = false;

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
     * @return AssertionCsvLoaderResult
     */
    public function loadFile()
    {
        if ($this->loaded) {
            return $this->result;
        }

        $this->loaded = false;

        $assertionClass = null;
        $testNames = [];
        $testNumbers = [];
        $ruleFileAsArray = [];
        $codeSnippets = [];

        $row = 0;
        if (($handle = fopen($this->ruleFilePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, self::SEPARATOR)) !== FALSE) {
                $row++;
                $ruleFileAsArray[] = $data;

                if ($row === 1) {
                    if ($data[0] !== 'class' || $data[1] === '') {
                        throw new LogicException(
                            "Erreur de format sur la ligne $row : la 1ere colonne doit contenir 'class' et la 2e la classe (FQCN) d'assertion à générer.");
                    }
                    $assertionClass = $data[1];
                    $testNumbers = array_slice($data, self::COLUMN_FOR_PRIVILEGE + 1, -2);
                    continue;
                }
                if ($row === 2) {
                    $testNames = array_slice($data, self::COLUMN_FOR_PRIVILEGE + 1, -2);
                    $testNumbers = array_combine($testNames, $testNumbers);
                    continue;
                }

                $line = $data[self::COLUMN_FOR_LINE];
                $enabled = (int) $data[self::COLUMN_FOR_ENABLED];
                $privilege = $data[self::COLUMN_FOR_PRIVILEGE];
                $message = $data[count($data) - 1];
                $return = $data[count($data) - 2];
                $data = array_slice($data, self::COLUMN_FOR_PRIVILEGE + 1, -2);

                if (! (bool)$enabled) {
                    continue;
                }

                $tmp = [];
                foreach ($data as $testIndex => $datum) {
                    if (strpos($datum, ':') === false) {
                        continue;
                    }
                    list($testOrder, $testValue) = explode(':', $datum);
                    $testName = $testNames[$testIndex];
                    $testNumber = $testNumbers[$testName];
                    $tmp[(int)$testOrder] = ['test' => $testName, 'value' => (int)$testValue, 'number' => $testNumber];
                }
                if (count($tmp) === 0) {
                    // cas où il n'y a aucune condition nécessaire (i.e. aucun test)
//                    $code = $this->generateReturn($return);
                    $codeSnippets[$privilege][$line] = [
                        'return' => $return,
                    ];
                } else {
                    ksort($tmp);
                    $tests = [];
                    foreach ($tmp as $item) {
                        $testName = $item['test'];
                        $negativeTest = $item['value'] ? false : true;
                        $testNumber = $item['number'];
                        $tests[$testName] = ['negative' => $negativeTest, 'number' => $testNumber];
                    }
//                    $code = $this->generateIfThen($tests, $return, $message);
                    $codeSnippets[$privilege][$line] = [
                        'tests' => $tests,
                        'return' => $return,
                        'message' => $message,
                    ];
                }
            }

            fclose($handle);
        }

        $result = new AssertionCsvLoaderResult();
        $result
            ->setRuleFilePath($this->ruleFilePath)
            ->setAssertionClass($assertionClass)
            ->setTestNames($testNames)
            ->setData($codeSnippets);

        $this->result = $result;
        $this->loaded = true;

        return $this->result;
    }
}