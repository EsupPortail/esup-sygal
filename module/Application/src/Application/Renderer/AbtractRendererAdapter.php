<?php

namespace Application\Renderer;

/**
 * Classe mère des "adapters" destinés à alimenter des macros de la bibliothèque "unicaen/renderer".
 *
 * Hériter de cette classe et créer les méthodes utiles seulement dans le cadre
 * de macros, pour ne pas polluer les classes métiers ou les services avec des choses orientées "affichage".
 */
abstract class AbtractRendererAdapter implements RendererAdapterInterface
{
    protected array $context = [];

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}