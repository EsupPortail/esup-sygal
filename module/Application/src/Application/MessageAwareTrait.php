<?php

namespace Application;

use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Util;

trait MessageAwareTrait
{
    protected array $messages = [];
    
    /**
     * Spécifie les messages courants (remplaçant les messages existants).
     */
    public function setMessages(array $messages): static
    {
        $this->messages = array();
        foreach ($messages as $severity => $message) {
            $this->addMessage($message, $severity);
        }
        return $this;
    }
    
    /**
     * Spécifie l'unique message courant.
     * 
     * @param string $message Message
     * @param string|null $severity Ex: MessageAwareInterface::INFO
     */
    public function setMessage(string $message, ?string $severity = null): static
    {
        return $this->setMessages(array($severity => $message));
    }
    
    /**
     * Indique si cette aide de vue contient des messages actuellement.
     * 
     * @param string|null $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     */
    public function hasMessages(?string $severity = null): bool
    {
        if ($severity !== null) {
            return array_key_exists($severity, $this->messages) && $this->messages[$severity];
        }
        return array_values($this->messages)[0] ?? false;
    }

    /**
     * Retourne les messages courants.
     *
     * @param string|null $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     */
    public function getMessages(?string $severity = null): array
    {
        if ($severity !== null) {
            return $this->messages[$severity] ?? [];
        }
        return $this->messages;
    }
    
    /**
     * Retourne les messages courants en une seule chaîne de caractères.
     * 
     * @param string $glue Séparateur à utiliser (PHP_EOL par défaut)
     * @param string|null $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     */
    public function getMessage(string $glue = PHP_EOL, ?string $severity = null): string
    {
        $messages = $this->getMessages($severity);
        
        return implode($glue, Util::extractArrayLeafNodes($messages));
    }

    /**
     * Ajoute un message.
     *
     * @param string $message  Message
     * @param string|null $severity Sévérité, ex: MessageAwareInterface::INFO
     * @param int|null $priority Permet d'ordonner les messages au sein d'une même sévérité
     */
    public function addMessage(string $message, ?string $severity = null, ?int $priority = 0): static
    {
        if (!$severity || !is_string($severity)) {
            $severity = MessageAwareInterface::INFO;
        }
        if (!isset($this->messages[$severity])) {
            $this->messages[$severity] = [];
        }
        if (!in_array($message, $this->messages[$severity])) {
            while (array_key_exists($priority, $this->messages[$severity])) { // recherche priorité disponible
                $priority++;
            }
            $this->messages[$severity][$priority] = $message;
            ksort($this->messages[$severity]);
        }
        return $this;
    }
    
    /**
     * Ajoute plusieurs messages.
     * 
     * @param array $messages [Sévérité => Message]
     */
    public function addMessages(array $messages): static
    {
        foreach ($messages as $severity => $message) {
            $this->addMessage($message, $severity);
        }
        
        return $this;
    }
    
    /**
     * Supprime tous les messages courants.
     * 
     * @param string|null $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     */
    public function clearMessages(?string $severity = null): static
    {
        if ($severity && array_key_exists($severity, $this->getMessages())) {
            $this->messages[$severity] = array();
        }
        else {
            $this->messages = array();
        }
        return $this;
    }
}