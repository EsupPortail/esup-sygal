<?php

namespace Application\Renderer;

/**
 * Interface des "adapters" destinés à alimenter des macros de la bibliothèque "unicaen/renderer".
 *
 * Implémenter cette interface dans une classe pour y implémenter les méthodes utiles seulement dans le cadre
 * de macros, pour ne pas polluer les classes métiers ou les services avec des choses orientées "affichage".
 */
interface RendererAdapterInterface
{
    public function setContext(array $context);
}