<?php

namespace Notification;

use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * @author Unicaen
 *
 * TODO : Devrait être déplacé dans UnicaenApp ou alors utiliser à la place MessageCollector.
 */
class MessageContainer implements MessageAwareInterface
{
    use MessageAwareTrait;
}