<?php

namespace UnicaenDeploy;

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
     * @var boolean
     */
    private $async = false;

    /**
     * @var boolean
     */
    private $dryRun = false;

    /**
     * @var bool Les arguments devront-ils être spécifiés avent le nom du script ?
     */
    private $argsBefore = false;

    /**
     * Constructor.
     *
     * @param string $scriptFilePath Chemin absolu du script à exécuter.
     * @param string $shellName 'sh', 'bash', 'ksh', etc.
     */
    function __construct(string $scriptFilePath, $shellName = 'sh')
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

        return $this;
    }

    /**
     * @param bool $dryRun
     * @return self
     */
    public function setDryRun(bool $dryRun = true): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * Lance le script en mettant la chaîne d'arguments avant le nom du script.
     * Ex: TMP_DIR=/tmp DEBUG=0 bash bin/mon_script.sh
     *
     * @param string $args
     * @return string
     */
    public function runWithArgsBefore(string $args = '')
    {
        $this->argsBefore = true;

        return $this->run($args);
    }

    /**
     * Lance le script avec la chaîne d'arguments spécifiés.
     *
     * @param string $args
     * @return string
     */
    public function run(string $args = '')
    {
        $this->checkPrerequisites();

        chdir($this->scriptDirPath);

        $command = $this->getCommandToString($args);

        if ($this->async) {
            $command = 'nohup ' . $command . ' > /dev/null 2>&1 &';
        }

        if ($this->dryRun) {
            return null;
        }

        // exécution de la commande
        exec($command, $output, $returnCode);

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
     * @param string $args
     * @return string
     */
    public function getCommandToString(string $args)
    {
        $scriptName = basename($this->scriptFilePath);

        if ($this->argsBefore) {
            return sprintf("%s %s %s", $args, $this->shell, $scriptName);
        } else {
            return sprintf("%s %s %s", $this->shell, $scriptName, $args);
        }
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