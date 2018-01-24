<?php

namespace Retraitement\Filter\Command;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;

abstract class AbstractCommand implements CommandInterface
{
    protected $options = [];
    protected $commandLine;
    protected $returnCode;
    protected $result;

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @throws RuntimeException En cas de ressources ou pré-requis manquants
     */
    public function checkResources()
    {

    }

    public function execute()
    {
        if (!$this->commandLine) {
            throw new LogicException("La ligne de commande n'a pas été générée, utilisez generate().");
        }

        exec($this->commandLine, $output, $returnCode);

        $this->returnCode = $returnCode;
        $this->result = $output;

        return $output;
    }

    /**
     * @return int|null
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}