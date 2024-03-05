<?php

namespace Admission\Event;

use Laminas\EventManager\Event;

class AdmissionEvent extends Event
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

    public function setMessages(array $messages): AdmissionEvent
    {
        $this->setParam('messages', $messages);
        return $this;
    }

    public function addMessages(array $messages): AdmissionEvent
    {
        $this->setMessages(array_merge_recursive($this->getMessages(), $messages));
        return $this;
    }
}