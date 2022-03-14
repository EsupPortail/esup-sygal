<?php

namespace Application\Validator;

use Application\Command\ShellCommandRunner;
use Application\Command\TestArchivabiliteShellCommandResult;
use Application\Command\TestArchivabiliteShellCommand;
use Application\Validator\Exception\CinesErrorException;
use Laminas\Validator\AbstractValidator;

class FichierCinesValidator extends AbstractValidator
{
    const INVALID = 'INVALID';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "Le fichier n'est pas archivable." . PHP_EOL . "%value%",
    );

    /**
     * @var TestArchivabiliteShellCommand
     */
    protected $shellCommand;

    /**
     * @var \Application\Command\TestArchivabiliteShellCommandResult
     */
    private $commandResult;

    /**
     * FichierCinesValidator constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->setOptions(['maxtime' => null]);
    }

    /**
     * @param TestArchivabiliteShellCommand $shellCommand
     * @return $this
     */
    public function setShellCommand(TestArchivabiliteShellCommand $shellCommand): self
    {
        $this->shellCommand = $shellCommand;

        return $this;
    }

    /**
     * @param string $value Chemin vers le fichier sur le disque
     * @return bool
     * @throws CinesErrorException
     */
    public function isValid($value): bool
    {
        $filepath = $value;
        $maxExecutionTime = $this->getOption('maxtime');

        /** @var TestArchivabiliteShellCommand $command */
        $this->shellCommand->setInputFilePath($filepath);
        $this->shellCommand->setMaxExecutionTime($maxExecutionTime);
        $this->shellCommand->generateCommandLine();
        $runner = new ShellCommandRunner();
        $runner->setCommand($this->shellCommand);
        $this->commandResult = $runner->runCommand();
        $result = $this->commandResult->getArrayResult();

        if (false === $result[TestArchivabiliteShellCommandResult::XML_TAG_ARCHIVABLE]) {
            $this->error(self::INVALID, $result[TestArchivabiliteShellCommandResult::XML_TAG_MESSAGE] ?: null);
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getArrayResult(): array
    {
        return $this->commandResult->getArrayResult();
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->commandResult->getResult();
    }
}