<?php

namespace Application\Command;

use InvalidArgumentException;
use RuntimeException;
use UnicaenApp\Exception\LogicException;

/**
 * Lanceur de script sh, bash, etc.
 *
 * @author Unicaen
 */
class ShellScriptRunner
{
    /**
     * @var string Chemin absolu du script à exécuter.
     */
    protected $scriptFilePath;

    /**
     * @var string Chemin absolu du répertoire contenant le script à exécuter.
     */
    protected $scriptDirPath;

    /**
     * @var string Nom du shell à utiliser ('sh', 'bash', 'ksh', etc.)
     */
    private $shell;

    /**
     * @var boolean
     */
    private $async = false;

    /**
     * @var boolean
     */
    private $hasRun = false;

    /**
     * @var int
     */
    private $returnCode;

    /**
     * Constructor.
     *
     * @param string $scriptFilePath Chemin absolu du script à exécuter.
     * @param string $shellName 'sh', 'bash', 'ksh', etc.
     */
    function __construct($scriptFilePath, $shellName = 'sh')
    {
        $this->shell = $shellName ?: 'sh';
        $this->scriptFilePath = $scriptFilePath;
        $this->scriptDirPath = dirname($scriptFilePath);
    }

    protected function checkPrerequisites()
    {
        if (! $this->scriptFilePath || ! is_readable($this->scriptFilePath)) {
            throw new InvalidArgumentException(
                "Le chemin spécifié pour le script à exécuter ne mène pas à un fichier lisible: " . $this->scriptFilePath);
        }
    }

    /**
     * @param bool $async
     * @return self
     */
    public function setAsync($async = true)
    {
        $this->async = $async;

        $this->returnCode = null;
        $this->hasRun = false;

        return $this;
    }

    /**
     * Lance le script.
     *
     * @param string $args Arguments éventuels
     * @return string Sortie texte retourné par l'exécution du script
     */
    public function run($args = '')
    {
        $this->checkPrerequisites();

        chdir($this->scriptDirPath);

        if ($this->async) {
            $command = $this->getAsyncCommandToString($args);
        } else {
            $command = $this->getCommandToString($args);
        }

        // exécution de la commande
        exec($command, $output, $returnCode);

        $this->returnCode = $returnCode;
        $this->hasRun = true;

        if ($this->async) {
            return null;
        }

        if (!is_array($output) || !isset($output[0])) {
            throw new RuntimeException(
                sprintf("La ligne de commande '%s' n'a retourné aucun résultat.", $command));
        }

        return trim($output[0]);
    }

    /**
     * Retourne le code de retour d'exécution du script.
     *
     * @return int
     */
    public function getReturnCode()
    {
        if ($this->hasRun === false) {
            throw new LogicException("La méthode run() doit être appelée auparavant");
        }

        return $this->returnCode;
    }

    /**
     * Retourne la commande exécutée.
     *
     * @param string $args Arguments éventuels de la ligne de commande
     * @return string
     */
    public function getCommandToString($args = '')
    {
        $scriptName = basename($this->scriptFilePath);

        return sprintf("%s %s %s", $this->shell, $scriptName, $args);
    }

    /**
     * Retourne la commande exécutée à la sauce asynchrone.
     *
     * @param string $args Arguments éventuels de la ligne de commande
     * @return string
     */
    public function getAsyncCommandToString($args = '')
    {
        $command = $this->getCommandToString($args);

        return 'nohup ' . $command . ' > /dev/null 2>&1 &';
    }

    /**
     * @return string
     */
    public function getScriptFilePath()
    {
        return $this->scriptFilePath;
    }

    /**
     * @return string
     */
    public function getScriptDirPath()
    {
        return $this->scriptDirPath;
    }
}