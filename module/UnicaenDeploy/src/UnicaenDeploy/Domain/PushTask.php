<?php

namespace UnicaenDeploy\Domain;

use UnicaenDeploy\ShellScriptRunner;

class PushTask extends Task
{
    const SCRIPT = 'push.sh';

    /**
     * @inheritDoc
     */
    public function run(array $args, string $binDir)
    {
        $cmd = $binDir . '/' . self::SCRIPT;

        // lancement du script
        $runner = new ShellScriptRunner($cmd);
        $runner->setDryRun();
        $result = $runner->runWithArgsBefore($this->argsToString($args));
        var_dump(
            $runner->getScriptDirPath(),
            $runner->getScriptFilePath(),
            $runner->getCommandToString($this->argsToString($args))
        );
        var_dump(__METHOD__, $cmd, $result);

        return $result;
    }
}