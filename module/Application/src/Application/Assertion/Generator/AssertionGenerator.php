<?php

namespace Application\Assertion\Generator;

use Application\Assertion\Loader\AssertionCsvLoader;
use Application\Assertion\Loader\AssertionCsvLoaderResult;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\AuthorTag;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Laminas\Code\Reflection\ClassReflection;
use ReflectionException;

class AssertionGenerator
{
    const HR = '//--------------------------------------------------------------------------------------';

    private AssertionCsvLoader $loader;
    protected ClassGenerator $classGenerator;
    private AssertionCsvLoaderResult $loadingResult;
    private string $commandLine;

    public function __construct(?AssertionCsvLoader $loader = null)
    {
        if ($loader !== null) {
            $this->setAssertionCsvLoader($loader);
        }
    }

    public function setAssertionCsvLoader(AssertionCsvLoader $loader): void
    {
        $this->loader = $loader;
    }

    public function setCommandLine(string $commandLine): void
    {
        $this->commandLine = $commandLine;
    }

    /**
     * Charge le fichier CSV contenant les règles
     * puis retourne le code PHP de la classe d'Assertion correspondante.
     */
    public function generate(): string
    {
        return $this->getClassGenerator()->generate();
    }
    
    /**
     * Charge le fichier CSV contenant les règles
     * puis retourne ClassGenerator permettant de générer le code PHP de la classe d'Assertion correspondante.
     */
    public function getClassGenerator(): ClassGenerator
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

    private function loadFile(): void
    {
        $this->loadingResult = $this->loader->loadFile();
    }


    protected function removeAllMethods(): void
    {
        foreach ($this->classGenerator->getMethods() as $methodGenerator) {
            $this->classGenerator->removeMethod($methodGenerator->getName());
        }
    }

    protected function addProperties(): void
    {
        if ($this->classGenerator->hasProperty('failureMessage')) {
            return;
        }
        if ($this->classGenerator->hasProperty('linesTrace')) {
            return;
        }

        $this->classGenerator->addProperties([
            new PropertyGenerator('failureMessage', null, PropertyGenerator::FLAG_PROTECTED, TypeGenerator::fromTypeString('?string')),
            new PropertyGenerator('linesTrace', [], PropertyGenerator::FLAG_PROTECTED, TypeGenerator::fromTypeString('array')),
        ]);
    }

    protected function addDockBlock(): void
    {
        $desc = sprintf("Générée avec la ligne de commande '%s'.", $this->commandLine);

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

    protected function addUses(): void
    {
        $this->classGenerator->addUse('Application\Assertion\Exception\UnexpectedPrivilegeException', 'UnexpectedPrivilegeException');

        foreach ($this->loadingResult->getUses() as $fqdn => $alias) {
            $this->classGenerator->addUse($fqdn, $alias);
        }
    }

    protected function addAssertAsBooleanMethod(): void
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
            'longDescription'  => '',
            'tags'             => [
                new ParamTag('privilege', 'string'),
                new ReturnTag('bool'),
            ],
        ]);

        $methodGen = new MethodGenerator($methodName, [new ParameterGenerator('privilege', 'string')], MethodGenerator::FLAG_PUBLIC, $body, $docblockGenerator);
        $methodGen->setReturnType('bool');
        $this->classGenerator->addMethodFromGenerator($methodGen);
    }

    protected function addTestMethods(): void
    {
        foreach ($this->loadingResult->getTestNames() as $testName) {
            $docblockGenerator = DocBlockGenerator::fromArray([
                'tags' => [
                    new ReturnTag('bool'),
                ],
            ]);

            $methodGen = new MethodGenerator($testName, [], MethodGenerator::FLAG_PROTECTED | MethodGenerator::FLAG_ABSTRACT, null, $docblockGenerator);
            $methodGen->setReturnType('bool');
            $this->classGenerator->addMethodFromGenerator($methodGen);
        }
    }

    protected function addLoadedFileContentMethod(): void
    {
        $methodName = 'loadedFileContent';

        if ($this->classGenerator->hasMethod($methodName)) {
            $this->classGenerator->removeMethod($methodName);
        }

        $docblockGenerator = DocBlockGenerator::fromArray([
            'shortDescription' => "Retourne le contenu du fichier CSV à partir duquel a été générée cette classe.",
            'longDescription'  => '',
            'tags'             => [
                new ReturnTag('string'),
            ],
        ]);

        $body = "return <<<'EOT'" . PHP_EOL . file_get_contents($this->loadingResult->getRuleFilePath()) . "EOT;";

        $methodGen = new MethodGenerator($methodName, [], MethodGenerator::FLAG_PUBLIC, $body, $docblockGenerator);
        $methodGen->setReturnType('string');
        $this->classGenerator->addMethodFromGenerator($methodGen);
    }

    protected function createClassGenerator(): void
    {
        $assertionClass = $this->loadingResult->getAssertionClass();

        try {
            $this->classGenerator = ClassGenerator::fromReflection(new ClassReflection($assertionClass));
        } catch (ReflectionException $e) {
            $this->classGenerator = new ClassGenerator($assertionClass);
            $this->classGenerator->setAbstract(true);
        }
    }

    protected function generateIfThen(array $tests, bool $return, ?string $failedAssertionMessage = null): string
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

    protected function generateTestForPrivilege(string $privilege, array $tests): string
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

        return implode(PHP_EOL, $parts);
    }

    protected function generateReturn(bool $return): string
    {
        return 'return ' . ($return ? 'true' : 'false') . ';';
    }

    protected function generateThrowUnexpectedPrivilegeException(): string
    {
        return
            'throw new UnexpectedPrivilegeException(' . PHP_EOL .
            $this->indent(4, '"Le privilège spécifié n\'est pas couvert par l\'assertion: $privilege. Trace : " . PHP_EOL . ' .
            'implode(PHP_EOL, $this->linesTrace)' .
            ');');
    }

    protected function indent(int $length, string $string): string
    {
        $prefix = str_repeat(' ', $length);

        $lines = explode(PHP_EOL, $string);
        $indentedLines = array_map(function ($line) use ($prefix) {
            return strlen($line) > 0 ? $prefix . $line : '';
        }, $lines);

        return implode(PHP_EOL, $indentedLines);
    }
}