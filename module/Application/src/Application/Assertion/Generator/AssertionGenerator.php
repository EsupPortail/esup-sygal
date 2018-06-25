<?php

namespace Application\Assertion\Generator;

use Application\Assertion\Loader\AssertionCsvLoader;
use Application\Assertion\Loader\AssertionCsvLoaderResult;
use ReflectionException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\AuthorTag;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\ClassReflection;

class AssertionGenerator
{
    const HR = '//--------------------------------------------------------------------------------------';

    /**
     * @var AssertionCsvLoader
     */
    private $loader;

    /**
     * @var ClassGenerator
     */
    protected $classGenerator;

    /**
     * @var AssertionCsvLoaderResult
     */
    private $loadingResult;

    /**
     * AssertionGenerator constructor.
     *
     * @param AssertionCsvLoader|null $loader
     */
    public function __construct(AssertionCsvLoader $loader = null)
    {
        if ($loader !== null) {
            $this->setAssertionCsvLoader($loader);
        }
    }

    /**
     * @param AssertionCsvLoader $loader
     */
    public function setAssertionCsvLoader(AssertionCsvLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Charge le fichier CSV contenant les règles
     * puis retourne le code PHP de la classe d'Assertion correspondante.
     *
     * @return string
     */
    public function generate()
    {
        return $this->getClassGenerator()->generate();
    }
    
    /**
     * Charge le fichier CSV contenant les règles
     * puis retourne ClassGenerator permettant de générer le code PHP de la classe d'Assertion correspondante.
     * 
     * @return ClassGenerator
     */
    public function getClassGenerator()
    {
        $this->loadFile();

        $this->createClassGenerator();

        $this->removeAllMethods();
        $this->addDockBlock();
        $this->addUses();
        $this->addProperties();
        $this->addAssertAsBooleanMethod();
        $this->addTestMethods();
        $this->addLoadedFileContentMethod();

        return $this->classGenerator;
    }

    private function loadFile()
    {
        $this->loadingResult = $this->loader->loadFile();
    }


    protected function removeAllMethods()
    {
        foreach ($this->classGenerator->getMethods() as $methodGenerator) {
            $this->classGenerator->removeMethod($methodGenerator->getName());
        }
    }

    protected function addProperties()
    {
        if ($this->classGenerator->hasProperty('failureMessage')) {
            return;
        }
        if ($this->classGenerator->hasProperty('linesTrace')) {
            return;
        }

        $this->classGenerator->addProperties([
            new PropertyGenerator('failureMessage', null, PropertyGenerator::FLAG_PROTECTED),
            new PropertyGenerator('linesTrace', [], PropertyGenerator::FLAG_PROTECTED),
        ]);
    }

    protected function addUses()
    {

    }

    protected function addDockBlock()
    {
        $desc = sprintf('Générée à partir du fichier %s.', realpath($this->loadingResult->getRuleFilePath()));

        $generator = DocBlockGenerator::fromArray([
            'shortDescription' => "Classe mère d'Assertion.",
            'longDescription'  => $desc,
            'tags'             => [
                new AuthorTag(get_class()),
                new GenericTag('date', date('d/m/Y H:i:s')),
            ],
        ]);

        $this->classGenerator->setDocBlock($generator);
    }

    protected function addAssertAsBooleanMethod()
    {
        $methodName = 'assertAsBoolean';
        $data = $this->loadingResult->getData();

        $phpParts = [];
        $phpParts[] = '$this->failureMessage = null;';
        foreach ($data as $privilege => $pieces) {
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

        $this->classGenerator->addMethodFromGenerator(
            new MethodGenerator($methodName, ['privilege'], MethodGenerator::FLAG_PUBLIC, $body, $docblockGenerator)
        );

        return true;
    }

    protected function addTestMethods()
    {
        foreach ($this->loadingResult->getTestNames() as $testName) {
            $docblockGenerator = DocBlockGenerator::fromArray([
                'tags' => [
                    new ReturnTag('bool'),
                ],
            ]);
            $this->classGenerator->addMethodFromGenerator(
                new MethodGenerator($testName, [], MethodGenerator::FLAG_PROTECTED | MethodGenerator::FLAG_ABSTRACT, null, $docblockGenerator)
            );
        }
    }

    protected function addLoadedFileContentMethod()
    {
        $methodName = 'loadedFileContent';

        if ($this->classGenerator->hasMethod($methodName)) {
            $this->classGenerator->removeMethod($methodName);
        }

        $docblockGenerator = DocBlockGenerator::fromArray([
            'shortDescription' => "Retourne le contenu du fichier CSV à partir duquel a été générée cette classe.",
            'longDescription'  => null,
            'tags'             => [
                new ReturnTag('string'),
            ],
        ]);

        $body = "return <<<'EOT'" . PHP_EOL . file_get_contents($this->loadingResult->getRuleFilePath()) . "EOT;";

        $this->classGenerator->addMethodFromGenerator(
            new MethodGenerator($methodName, [], MethodGenerator::FLAG_PUBLIC, $body, $docblockGenerator)
        );
    }

    protected function createClassGenerator()
    {
        $assertionClass = $this->loadingResult->getAssertionClass();

        try {
            $this->classGenerator = ClassGenerator::fromReflection(new ClassReflection($assertionClass));
        } catch (ReflectionException $e) {
            throw new RuntimeException("Erreur lors de la création du ClassGenerator", null, $e);
        }
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
        foreach ($tests as $line => $data) {
            $lineComment = sprintf('/* line %d */', $line);
            $parts[] = $this->indent(4, $lineComment);
            $parts[] = $this->indent(4, '$this->linesTrace[] = \'' . $lineComment . '\';');
            if (isset($data['tests'])) {
                $code = $this->generateIfThen($data['tests'], $data['return'], $data['message']);
            } else {
                $code = $this->generateReturn($data['return']);
            }
            $parts[] = $this->indent(4, $code);
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
}