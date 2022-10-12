<?php

namespace StepStar\Service\Xslt;

use Saxon\SaxonProcessor;
use Saxon\Xslt30Processor;

class XsltService
{
    protected SaxonProcessor $saxonProcessor;
    protected Xslt30Processor $transformer;
    protected string $outputDir;
    protected string $xslFilePath;

    /**
     * Spécifie le chemin du répertoire cible des fichiers XML résultats.
     *
     * @param string $outputDir
     * @return self
     */
    public function setOutputDir(string $outputDir): self
    {
        $this->outputDir = $outputDir;
        return $this;
    }

    /**
     * Spécifie le chemin du fichier XSL de transformation à utiliser.
     *
     * @param mixed $xslFilePath
     * @return self
     */
    public function setXslFilePath(string $xslFilePath): self
    {
        $this->xslFilePath = $xslFilePath;
        return $this;
    }

    /**
     * Génération des fichiers XML TEF dans le répertoire de sortie spécifié via {@see setOutputDir()}.
     *
     * @param string $thesesXmlFilepath Fichier XML source
     */
    public function transformToFiles(string $thesesXmlFilepath)
    {
        // il faut spécifier un fichier mais c'est son répertoire qui recevra les fichiers générés
        $outputFilepath = $this->outputDir . '/' . uniqid('saxonc_') . '.xml';

        // invoke the kill function during shutdown
        register_shutdown_function([self::class, 'killCurrentThread']); // NE CHANGE RIEN :-(

        $this->saxonProcessor = new SaxonProcessor();
        $this->transformer = $this->saxonProcessor->newXslt30Processor();
        $this->transformer->setInitialMatchSelectionAsFile($thesesXmlFilepath);
        $this->transformer->applyTemplatesReturningFile($this->xslFilePath, $outputFilepath);
        $this->transformer->clearParameters();
        $this->transformer->clearProperties();
        unlink($outputFilepath);
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->transformer->getExceptionCount() > 0;
    }

    /**
     * @return array [code => message]
     */
    public function getErrors(): array
    {
        $errors = [];

        $errCount = $this->transformer->getExceptionCount();
        if ($errCount > 0) {
            for ($i = 0; $i < $errCount; $i++) {
                $errCode = $this->transformer->getErrorCode($i);
                $errMessage = $this->transformer->getErrorMessage($i);
                $errors[$errCode] = $errMessage;
            }
            $this->transformer->exceptionClear();
        }

        return $errors;
    }

    /**
     * @see https://saxonica.plan.io/issues/4371
     *
     * @param int $minutes - minutes to wait until process is killed
     * @return bool
     */
    static public function killCurrentThread(int $minutes = 0): bool
    {
        if (!stristr(php_sapi_name(), 'fpm-')) {
            return false;
        }

        $signal = SIGTERM;
        $pid = posix_getpid();

        // get a list of child processes for the current running process
        $cmd = "pstree -p {$pid} | grep -o '([0-9]\+)' | grep -o '[0-9]\+'";
        exec($cmd, $processlist);

        $processlist = array_map('intval', $processlist);
        $processlist = array_filter($processlist, function ($cid) use ($pid) {
            return $cid !== $pid;
        });

        $processlist = array_filter($processlist, function ($cid) {
            return posix_kill($cid, 0);
        });

        if (!count($processlist)) {
            return false;
        }

        $firstchild = array_shift($processlist);

        $prefix = sprintf('rmproc.%s.%s.', $firstchild, uniqid(true));
        $tmpfile = tempnam(sys_get_temp_dir(), $prefix);
        chmod($tmpfile, 0755);

        $script = <<<BASH
#!/bin/sh

PID={$firstchild}
sleep 5 && kill -s {$signal} \$PID  > /dev/null 2>&1
rm \$0
BASH;

        file_put_contents($tmpfile, $script);
        $cmd = sprintf(
            '/usr/bin/at now +%d min -f %s 2>&1 | /usr/bin/logger -i &',
            $minutes,
            $tmpfile
        );

        exec($cmd);
        return true;
    }
}