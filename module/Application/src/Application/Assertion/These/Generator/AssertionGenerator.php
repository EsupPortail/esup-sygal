<?php

namespace Application\Assertion\These\Generator;

use ReflectionException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\ClassReflection;

abstract class AssertionGenerator
{
    const COLUMN_LINE = 0;
    const COLUMN_PRIVILEGE = 1;

    const HR = '//--------------------------------------------------------------------------------------';

    /**
     * @var string
     */
    protected $ruleFilePath;

    /**
     * @var string
     */
    protected $assertionClass;

    /**
     * @var ClassGenerator
     */
    protected $assertionClassGenerator;

    /**
     * @var string[]
     */
    protected $testNames;

    /**
     * @var string[]
     */
    protected $testNumbers;

    /**
     * @var array[]
     */
    protected $codeSnippets;

    /**
     * AssertionGenerator constructor.
     *
     * @param string $ruleFilePath
     * @param string $assertionClass
     */
    public function __construct($ruleFilePath, $assertionClass)
    {
        $this->ruleFilePath = $ruleFilePath;
        $this->assertionClass = $assertionClass;
    }

    /**
     * @return string
     */
    public function generateCode()
    {
        $this->loadFile();

        $this->createAssertionClassGenerator();

        $this->removeAllMethods();
        $this->addDockBlock();
        $this->addUses();
        $this->addProperties();
        $this->addAssertAsBooleanMethod();
        $this->addTestMethods();
        $this->addLoadedFileContentMethod();

        return $this->assertionClassGenerator->generate();
    }

    /**
     * @param string $filePath
     */
    public function writeToFile($filePath)
    {
        $code = $this->generateCode();
        $content = '<?php' . PHP_EOL . PHP_EOL . $code;

        file_put_contents($filePath, $content);
    }

    protected function createAssertionClassGenerator()
    {
        try {
            $this->assertionClassGenerator = ClassGenerator::fromReflection(new ClassReflection($this->assertionClass));
        } catch (ReflectionException $e) {
            throw new RuntimeException("Erreur lors de la création du ClassGenerator", null, $e);
        }
    }

    protected function loadFile()
    {
        $testNames = [];
        $testNumbers = [];
        $ruleFileAsArray = [];
        $codeSnippets = [];

        $row = 0;
        if (($handle = fopen($this->ruleFilePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                $ruleFileAsArray[] = $data;

                if ($row === 1) {
                    $testNumbers = array_slice($data, self::COLUMN_PRIVILEGE + 1, -2);
                    continue;
                }
                if ($row === 2) {
                    $testNames = array_slice($data, self::COLUMN_PRIVILEGE + 1, -2);
                    $testNumbers = array_combine($testNames, $testNumbers);
                    continue;
                }

                $line = $data[self::COLUMN_LINE];
                $privilege = $data[self::COLUMN_PRIVILEGE];
                $message = $data[count($data) - 1];
                $return = $data[count($data) - 2];
                $data = array_slice($data, self::COLUMN_PRIVILEGE + 1, -2);

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
                    $code = $this->generateReturn($return);
                    $codeSnippets[$privilege][$line] = $code;
                } else {
                    ksort($tmp);
                    $tests = [];
                    foreach ($tmp as $item) {
                        $testName = $item['test'];
                        $negativeTest = $item['value'] ? false : true;
                        $testNumber = $item['number'];
                        $tests[$testName] = ['negative' => $negativeTest, 'number' => $testNumber];
                    }
                    $code = $this->generateIfThen($tests, $return, $message);
                    $codeSnippets[$privilege][$line] = $code;
                }
            }

            fclose($handle);
        }

        $this->testNames = $testNames;
        $this->testNumbers = $testNumbers;
        $this->codeSnippets = $codeSnippets;
    }

    /**
     * @param array  $tests
     * @param bool   $return
     * @param string $failedAssertionMessage
     * @return string
     */
    protected function generateIfThen(array $tests, $return, $failedAssertionMessage = null)
    {
        $testParts = [];
        foreach ($tests as $testName => $config) {
            $negative = $config['negative'];
            $number = $config['number'];
            $testParts[] = sprintf("%s\$this->%s() /* test %d */",
                $negative ? '! ' : '',
                $testName,
                $number);
        }
        $ifContent = implode(" && " . PHP_EOL, $testParts);

        $bodyParts = [];
        if (! $return && $failedAssertionMessage) {
            $bodyParts[] = '$this->failureMessage = "' . $failedAssertionMessage . '";';
        }
        $bodyParts[] = $this->generateReturn($return);
        $bodyContent = implode(PHP_EOL, $bodyParts);

        return
            "if (" . ltrim($this->indent(4, $ifContent)) . ") {" . PHP_EOL .
            $this->indent(4, $bodyContent) . PHP_EOL .
            "}";
    }

    /**
     * @param string $privilege
     * @param array  $tests
     * @return string
     */
    protected function generateTestForPrivilege($privilege, array $tests)
    {
        if ($privilege === '*') {
            $if = '$privilege';
        } else {
            $if = sprintf('$privilege === %s', $privilege);
        }

        $parts = [];
        $parts[] = sprintf('if (%s) {', $if);
        $parts[] = self::HR;
        foreach ($tests as $line => $test) {
            $line = sprintf('/* line %d */', $line);
            $parts[] = $this->indent(4, $line);
            $parts[] = $this->indent(4, '$this->linesTrace[] = \'' . $line . '\';');
            $parts[] = $this->indent(4, $test);
        }
        $parts[] = '}';

        $code = implode(PHP_EOL, $parts);

        return $code;
    }

    /**
     * @param bool $return
     * @return string
     */
    protected function generateReturn($return)
    {
        return 'return ' . ($return ? 'true' : 'false') . ';';
    }

    /**
     * @return string
     */
    protected function generateThrowUnexpectedPrivilegeException()
    {
        return
            'throw new \Application\Assertion\Exception\UnexpectedPrivilegeException(' . PHP_EOL .
            $this->indent(4, '"Le privilège spécifié n\'est pas couvert par l\'assertion: $privilege. Trace : " . PHP_EOL . ' .
            'implode(PHP_EOL, $this->linesTrace)' .
            ');');
    }

    /**
     * @param int    $length
     * @param string $string
     * @return string
     */
    protected function indent($length, $string)
    {
        $prefix = str_repeat(' ', $length);

        $lines = explode(PHP_EOL, $string);
        $indentedLines = array_map(function ($line) use ($prefix) {
            return strlen($line) > 0 ? $prefix . $line : '';
        }, $lines);

        return implode(PHP_EOL, $indentedLines);
    }

    protected function removeAllMethods()
    {
        foreach ($this->assertionClassGenerator->getMethods() as $methodGenerator) {
            $this->assertionClassGenerator->removeMethod($methodGenerator->getName());
        }
    }

    protected function addProperties()
    {
        if ($this->assertionClassGenerator->hasProperty('failureMessage')) {
            return;
        }
        if ($this->assertionClassGenerator->hasProperty('linesTrace')) {
            return;
        }

        $this->assertionClassGenerator->addProperties([
            new PropertyGenerator('failureMessage', null, PropertyGenerator::FLAG_PROTECTED),
            new PropertyGenerator('linesTrace', [], PropertyGenerator::FLAG_PROTECTED),
        ]);
    }

    abstract protected function addUses();
//    {
//        $this->assertionClassGenerator->addUse(DoctorantPrivileges::class);
//        $this->assertionClassGenerator->addUse(ThesePrivileges::class);
//        $this->assertionClassGenerator->addUse(ValidationPrivileges::class);
//    }

    protected function addDockBlock()
    {
        $desc = sprintf('Générée le %s par %s à partir du fichier %s.',
            date('d/m/Y H:i:s'),
            get_class() . PHP_EOL,
            $this->ruleFilePath
        );

        $this->assertionClassGenerator->setDocBlock(DocBlockGenerator::fromArray([
            'shortDescription' => 'Classe mère pour les assertions.',
            'longDescription'  => $desc,
        ]));
    }

    protected function addAssertAsBooleanMethod()
    {
        $methodName = 'assertAsBoolean';

        $phpParts = [];
        $phpParts[] = '$this->failureMessage = null;';
        foreach ($this->codeSnippets as $privilege => $pieces) {
            $phpParts[] = $this->generateTestForPrivilege($privilege, $pieces);
        }
        $body = implode(PHP_EOL . PHP_EOL, $phpParts) . PHP_EOL . PHP_EOL;

        $body .= $this->generateThrowUnexpectedPrivilegeException() . PHP_EOL . PHP_EOL;

        $docblockGenerator = DocBlockGenerator::fromArray([
            'shortDescription' => "Retourne true si le privilège spécifié est accordé ; false sinon.",
            'longDescription'  => null,
            'tags'             => [
                new ParamTag('privilege', 'string'),
                new ReturnTag('bool'),
            ],
        ]);

        $this->assertionClassGenerator->addMethodFromGenerator(
            new MethodGenerator($methodName, ['privilege'], MethodGenerator::FLAG_PUBLIC, $body, $docblockGenerator)
        );

        return true;
    }

    protected function addTestMethods()
    {
        foreach ($this->testNames as $testName) {

            $docblockGenerator = DocBlockGenerator::fromArray([
//                'shortDescription' => "Retourne true si l'utilisateur courant possède le privilège spécifié ; false sinon.",
//                'longDescription' => null,
                'tags' => [
                    new ReturnTag('bool'),
                ],
            ]);

            $this->assertionClassGenerator->addMethodFromGenerator(
                new MethodGenerator($testName, [], MethodGenerator::FLAG_PROTECTED | MethodGenerator::FLAG_ABSTRACT, null, $docblockGenerator)
            );
        }
    }

    protected function addLoadedFileContentMethod()
    {
        $methodName = 'loadedFileContent';

        if ($this->assertionClassGenerator->hasMethod($methodName)) {
            $this->assertionClassGenerator->removeMethod($methodName);
        }

        $docblockGenerator = DocBlockGenerator::fromArray([
            'shortDescription' => "Retourne le contenu du fichier CSV à partir duquel a été générée cette classe.",
            'longDescription'  => null,
            'tags'             => [
                new ReturnTag('string'),
            ],
        ]);

        $body = "return <<<'EOT'" . PHP_EOL . file_get_contents($this->ruleFilePath) . "EOT;";

        $this->assertionClassGenerator->addMethodFromGenerator(
            new MethodGenerator($methodName, [], MethodGenerator::FLAG_PUBLIC, $body, $docblockGenerator)
        );
    }
}