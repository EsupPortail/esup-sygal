<?php

namespace Application\Command;

use Application\Command\Exception\TimedOutCommandException;
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
     * @param string $name
     * @param mixed  $value
     * @return self
     */
    public function setOption($name, $value)
    {
        return $this->setOptions([$name => $value]);
    }

    /**
     * @throws RuntimeException En cas de ressources ou pré-requis manquants
     */
    public function checkResources()
    {

    }

    /**
     * @return string
     * @throws TimedOutCommandException Si un timeout d'exécution a été défini et qu'il a expiré
     */
    public function execute()
    {
        if (!$this->commandLine) {
            throw new LogicException("La ligne de commande n'a pas été générée, utilisez generate().");
        }

        // l'option 'timeout' active l'exécution avec une limite de temps.
        $timeout = isset($this->options['timeout']) ? $this->options['timeout'] : null;
        if ($timeout) {
            // ex: '60s', '1m', '2h', '1d'. Cf. "man timeout".
            $this->commandLine = "timeout --signal=HUP $timeout " . $this->commandLine;
        }

        exec($this->commandLine, $output, $returnCode);

        // un code retour 124 indique que la commande a été exécutée avec un timeout et que ce timeout a été atteint
        if ($timeout && $returnCode === 124) {
            $toce = new TimedOutCommandException();
            $toce->setTimeout($timeout);
            throw $toce;
        }

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

    /**
     * Retourne la ligne de commande générée par generate().
     *
     * @return string
     */
    public function getCommandLine()
    {
        return $this->commandLine;
    }
}