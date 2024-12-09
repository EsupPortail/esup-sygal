<?php

namespace Application\Renderer\Template\Variable;

use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

/**
 * Classe mère des "variables de template".
 *
 * Une variable de template a vocation à être transmise à un template (cf. unicaen/renderer), afin d'alimenter
 * des macros.
 *
 * Une variable de template fonctionne à partir d'une instance "cible" (centrale), exemples :
 * une instance de {@see \These\Entity\Db\These} pour une variable de template de type
 * {@see \These\Renderer\TheseTemplateVariable}.
 *
 * Lorsque la propriété ou la méthode appelée n'existe pas, l'appel est transmise à l'instance cible.
 *
 * Héritez de cette classe et ajoutez les méthodes utiles seulement dans le cadre
 * de macros, pour ne pas polluer les classes métiers ou les services avec des choses orientées "affichage".
 */
abstract class AbstractTemplateVariable implements TemplateVariableInterface
{
    protected TemplateVariablePluginManager $pluginManager;
    protected object $target;
    protected array $context = [];

    public function setPluginManager(TemplateVariablePluginManager $pluginManager): void
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Spécifie l'instance métier "cible" (centrale) de cette variable de template.
     * Exemple : ce serait typiquement une instance de These pour une variable de template de type
     * TheseTemplateVariable.
     */
    public function setTarget(object $target): void
    {
        $this->target = $target;
    }

    /**
     * Transmission de contexte sous la forme d'un tableau associatif.
     * **NB : Vous êtes plutôt encouragés à créer des setter explicites !**
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}