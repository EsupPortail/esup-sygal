<?php

namespace Application\Assertion\Loader;

use RuntimeException;
use UnicaenApp\Exception\LogicException;

class AssertionCsvLoader
{
    const SEPARATOR = ';';
    const COLUMN_FOR_LINE = 0;
    const COLUMN_FOR_ENABLED = 1;
    const COLUMN_FOR_PRIVILEGE = 2;

    private string $ruleFilePath;
    private bool $loaded = false;
    private AssertionCsvLoaderResult $result;

    public function setRuleFilePath(string $ruleFilePath): self
    {
        $this->ruleFilePath = $ruleFilePath;
        $this->loaded = false;

        return $this;
    }

    public function getRuleFilePath(): string
    {
        return $this->ruleFilePath;
    }

    public function loadFile(): AssertionCsvLoaderResult
    {
        if ($this->loaded) {
            return $this->result;
        }

        $this->loaded = false;

        $uses = [];
        $assertionClass = null;
        $testNames = [];
        $testNumbers = [];
        $codeSnippets = [];

        if (($handle = fopen($this->ruleFilePath, "r")) === FALSE) {
            throw new RuntimeException("Impossible d'ouvrir le fichier $this->ruleFilePath en lecture !");
        }

        $realRowNumber = 0;
        $relativeRowNumber = 0;

        while (($data = fgetcsv($handle, 1000, self::SEPARATOR)) !== FALSE) {
            $realRowNumber++;

            if ($relativeRowNumber === 0) {
                if ($data[0] === 'use' && $data[1] !== '' && $data[2] !== '') {
                    $useClass = $data[1];
                    $useAlias = $data[2];
                    $uses[$useClass] = $useAlias;

                    continue;
                }
            }

            $relativeRowNumber++;
            
            if ($relativeRowNumber === 1) {
                if ($data[0] !== 'class' || $data[1] === '') {
                    throw new LogicException(
                        "Erreur de format sur la ligne $realRowNumber : la 1ere colonne doit contenir 'class' et la 2e la classe (FQCN) d'assertion à générer.");
                }
                $assertionClass = $data[1];
                $testNumbers = array_slice($data, self::COLUMN_FOR_PRIVILEGE + 1, -2);

                continue;
            }

            if ($relativeRowNumber === 2) {
                $testNames = array_slice($data, self::COLUMN_FOR_PRIVILEGE + 1, -2);

                // Il peut y avoir des colonnes vides donc des noms de tests vides :
                // on supprime les numéros de colonnes correspondant aux noms de tests vides...
                foreach ($testNames as $index => $testName) {
                    if ($testName === '') {
                        unset($testNumbers[$index]);
                    }
                }
                // puis on oublie les noms de test vides.
                $testNames = array_filter($testNames);

                $testNumbers = array_combine($testNames, $testNumbers);

                continue;
            }

            $line = $data[self::COLUMN_FOR_LINE];
            $enabled = (int) $data[self::COLUMN_FOR_ENABLED];
            $privilege = $data[self::COLUMN_FOR_PRIVILEGE];
            $message = $data[count($data) - 1];
            $return = $data[count($data) - 2];
            $data = array_slice($data, self::COLUMN_FOR_PRIVILEGE + 1, -2);

            if (!$enabled) {
                continue;
            }

            $tmp = [];
            foreach ($data as $testIndex => $datum) {
                if (!str_contains($datum, ':')) {
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

        $result = new AssertionCsvLoaderResult();
        $result
            ->setRuleFilePath($this->ruleFilePath)
            ->setAssertionClass($assertionClass)
            ->setTestNames($testNames)
            ->setUses($uses)
            ->setData($codeSnippets);

        $this->result = $result;
        $this->loaded = true;

        return $this->result;
    }
}