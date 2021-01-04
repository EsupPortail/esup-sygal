<?php

namespace UnicaenDeploy\Domain;

use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class CopyTask extends Task
{
    /**
     * @inheritDoc
     */
    public function run(array $args, string $binDir)
    {
        var_dump(shell_exec('whoami'));
        var_dump(shell_exec('pwd'));
        var_dump(passthru('cat /root/.ssh/config'));
//        $from = '/home/gauthierb/workspace/sygal/deploy/targets/preprod/config/php/fpm/conf.d/99-sygal.ini';
//        $to = '/tmp/99-sygal.ini';
//        $cmd = "scp $from root@usygalpp1.unr-runn.fr:$to 2>&1";
//        exec($cmd, $out, $r);
//
//        exec("whoami", $out, $r);
//
//        $cmd = "ssh root@usygalpp1.unr-runn.fr whoami 2>&1";
//        exec($cmd, $out, $r);

        $connection = $this->sshConnect();

        $results = [];
        $files = $this->config['files'];
        foreach ($files as $src => $dst) {
            $src = $this->fillVarsInString($src, $args);
            $dst = $this->fillVarsInString($dst, $args);
            if (substr($src, 0, 1) !== '/') {
                Assert::keyExists($this->config, 'source_dir', "Le répertoire source est absent de la config");
                $sourceDir = $this->config['source_dir'];
                Assert::directory($sourceDir);
                $src = realpath($sourceDir) . '/' . $src;
                Assert::fileExists($src);
            }
            $ok = $this->sshCopy($src, $dst, $connection);
            $results[$src] = $ok;
        }

        return $results;
    }

    /**
     * @param string $src
     * @param string $dst
     * @param resource $connection
     * @return bool
     */
    private function sshCopy(string $src, string $dst, $connection)
    {
        $ok = ssh2_scp_send($connection, $src, $dst);
        if (! $ok) {
            throw new RuntimeException("Echec de la copie de fichier");
        }

        var_dump(__METHOD__, $src, $dst, $ok);

        return $ok;
    }
}