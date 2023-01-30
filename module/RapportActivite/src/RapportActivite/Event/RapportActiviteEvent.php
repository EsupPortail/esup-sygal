<?php

namespace RapportActivite\Event;

use Laminas\EventManager\Event;

class RapportActiviteEvent extends Event
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

    public function addMessages(array $messages): RapportActiviteEvent
    {
        $this->setMessages(array_merge_recursive($this->getMessages(), $messages));
        return $this;
    }
}