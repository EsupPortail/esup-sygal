<?php

namespace RapportActivite\Event;

use Laminas\EventManager\Event;

abstract class RapportActiviteEvent extends Event
{
    /**
     * Retourne les éventuels messages positionnés par les listeners.
     *
     * @return string[] Ex : ['success' => "Succès !", 'warning' => "NB : bla bla"]
     */
    public function getMessages(): array
    {
        return $this->getParam('messages', []);
    }

    public function setMessages(array $messages): RapportActiviteEvent
    {
        $this->setParam('messages', $messages);
        return $this;
    }
}