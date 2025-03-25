<?php

namespace InscriptionAdministrative\Process;

abstract class AbstractProcess extends \Application\Process\AbstractProcess
{
    /**
     * Point d'entrée.
     *
     * @throws \Exception
     */
    abstract public function run();

}