<?php

namespace UnicaenDeploy\Domain;

use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class RunTask extends Task
{
    /**
     * @inheritDoc
     */
    public function run(array $args, string $binDir)
    {
        Assert::keyExists($this->config, 'commands');
        $commands = $this->config['commands'];

        Assert::keyExists($args, 'HOSTNAME');
        Assert::keyExists($args, 'USERNAME');

        $connection = $this->sshConnect($args['HOSTNAME'], $args['USERNAME']);

        $results = [];
        foreach ($commands as $command) {
            $command = $this->fillVarsInString($command, $args);
            $output = $this->sshExec($command, $connection);
            $results[$command] = $output;
        }

        return $results;
    }

    /**
     * @param string $command
     * @param resource $connection
     * @return string
     */
    private function sshExec(string $command, $connection)
    {
        $stream = ssh2_exec($connection, $command);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        if ($stream === false) {
            throw new RuntimeException("Erreur lors de l'obtention du flux de sortie standard");
        }
        if ($errorStream === false) {
            throw new RuntimeException("Erreur lors de l'obtention du flux d'erreur standard");
        }

        stream_set_blocking($stream, true);
        stream_set_blocking($errorStream, true);

        $out = stream_get_contents($stream);
        $err = stream_get_contents($errorStream);
        if ($out === false) {
            throw new RuntimeException("Erreur lors de la lecture du flux de sortie standard");
        }
        if ($err === false) {
            throw new RuntimeException("Erreur lors de la lecture du flux d'erreur standard");
        }
        if ($err) {
            throw new RuntimeException("Erreur distante rencontrée lors de l'exécution de la commande : " . $err);
        }

        fclose($stream);
        fclose($errorStream);

        return $out;
    }
}