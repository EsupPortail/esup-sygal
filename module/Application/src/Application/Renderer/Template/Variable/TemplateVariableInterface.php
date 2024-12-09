<?php

namespace Application\Renderer\Template\Variable;

use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

/**
 * Interface des "variables de template" transmises à un template, afin d'alimenter des macros.
 *
 * Implémenter cette interface dans une classe pour y implémenter les méthodes utiles seulement dans le cadre
 * de macros, pour ne pas polluer les classes métiers ou les services avec des choses orientées "affichage".
 */
interface TemplateVariableInterface
{
    /**
     * Transmission du plugin manager à cette instance.
     */
    public function setPluginManager(TemplateVariablePluginManager $pluginManager): void;

    /**
     * Spécifie l'instance métier "cible" (centrale) de cette variable de template.
     * Exemple : ce serait typiquement une instance de These pour une variable de template de type
     * TheseTemplateVariable.
     */
    public function setTarget(object $target): void;

    /**
     * Transmission de contexte sous la forme d'un tableau associatif.
     * **NB : Vous êtes plutôt encouragés à créer des setter explicites !**
     */
    public function setContext(array $context);
}