<?php

namespace Application\Command\Exception;

use UnicaenApp\Exception\RuntimeException;

class CommandExecutionException extends RuntimeException
{
    static public function unknown()
    {
        return new static("Erreur inconnue.");
    }

    static public function emptyResult()
    {
        return new static("La ligne de commande n'a retourné aucun résultat.");
    }

    static public function operationTimedout()
    {
        return new static("Le web service n'a pas répondu dans le délai maximum imparti.");
    }

    static public function gotNothing()
    {
        return new static("Le web service n'a retourné aucune donnée.");
    }
}