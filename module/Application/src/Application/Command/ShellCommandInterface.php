<?php

namespace Application\Command;

interface ShellCommandInterface
{
    /**
     * Retourne le "petit nom" de cette commande.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Spécifie les options de fonctionnement de cette commande.
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Génère la ligne de commande à lancer.
     */
    public function generateCommandLine();

    /**
     * Vérification que les prérequis pour lancer la commande sont réunis.
     *
     * @throws \Application\Command\Exception\ShellCommandException En cas de problème
     */
    public function checkRequirements();

    /**
     * Retourne la ligne de commande générée si {@see generateCommandLine()} a été appelée auparavant.
     *
     * @return string
     */
    public function getCommandLine(): string;

    /**
     * @param array $output
     * @param int $returnCode
     * @return ShellCommandResultInterface
     */
    public function createResult(array $output, int $returnCode);
}