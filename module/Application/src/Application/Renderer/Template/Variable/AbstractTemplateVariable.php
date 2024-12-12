<?php

namespace Application\Renderer\Template\Variable;

/**
 * Classe mère d'une "variable de template" transmise à un template, afin d'alimenter des macros.
 *
 * Hériter de cette classe et créer les méthodes utiles seulement dans le cadre
 * de macros, pour ne pas polluer les classes métiers ou les services avec des choses orientées "affichage".
 */
abstract class AbstractTemplateVariable implements TemplateVariableInterface
{
    protected array $context = [];

    /**
     * Transmission de contexte sous la forme d'un tableau associatif.
     * **NB : Vous êtes plutôt encouragés à créer des setter explicites !**
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}