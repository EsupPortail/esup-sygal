<?php

namespace Application\Validator;

use Application\Command\ValidationFichierCinesCommand;
use Application\Validator\Exception\CinesErrorException;
use Zend\Validator\AbstractValidator;

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
     * @var ValidationFichierCinesCommand
     */
    protected $command;

    /**
     * @param ValidationFichierCinesCommand $command
     * @return $this
     */
    public function setCommand(ValidationFichierCinesCommand $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @param string $filepath Chemin vers le fichier sur le disque
     * @return bool
     * @throws CinesErrorException
     */
    public function isValid($filepath)
    {
        $this->command->execute($filepath);

        $result = $this->command->getArrayResult();

        if (false === $result[ValidationFichierCinesCommand::XML_TAG_ARCHIVABLE]) {
            $this->error(self::INVALID, $result[ValidationFichierCinesCommand::XML_TAG_MESSAGE] ?: null);
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getArrayResult()
    {
        return $this->command->getArrayResult();
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->command->getResult();
    }
}