<?php

namespace Application\Command;

use Application\Command\Exception\TimedOutCommandException;
use UnicaenApp\Exception\RuntimeException;

interface CommandInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value);

    /**
     * @param string $outputFilePath
     * @param string $inputFilePath
     * @param string $errorFilePath
     * @return string
     */
    public function generate($outputFilePath, $inputFilePath, &$errorFilePath = null);

    /**
     * @throws RuntimeException En cas de problème
     */
    public function checkResources();

    /**
     * @return string
     * @throws TimedOutCommandException Si un timeout d'exécution a été défini et qu'il a expiré
     */
    public function execute();

    /**
     * @return int|null
     */
    public function getReturnCode();

    /**
     * @return mixed
     */
    public function getResult();

    /**
     * Retourne la ligne de commande générée par generate().
     *
     * @return string
     */
    public function getCommandLine();
}