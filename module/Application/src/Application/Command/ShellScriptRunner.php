<?php

namespace Application\Command;

use InvalidArgumentException;
use RuntimeException;

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
     * Lance le script.
     *
     * @param string $args
     * @return string
     */
    public function run($args = '')
    {
        $this->checkPrerequisites();

//        $scriptName = basename($this->scriptFilePath);

        chdir($this->scriptDirPath);

//        $command = sprintf("%s %s %s", $this->shell, $scriptName, $args);
        $command = $this->getCommandToString($args);

        // exécution de la commande
        exec($command, $output, $returnCode);

        if (!is_array($output) || !isset($output[0])) {
            throw new RuntimeException(
                sprintf("La ligne de commande '%s' n'a retourné aucun résultat.", $command));
        }

        return trim($output[0]);
    }

    /**
     * @param string $args
     * @return string
     */
    public function getCommandToString($args)
    {
        $scriptName = basename($this->scriptFilePath);

        return sprintf("%s %s %s", $this->shell, $scriptName, $args);
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