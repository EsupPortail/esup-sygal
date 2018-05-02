<?php

namespace Notification;

use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * @author Unicaen
 * @deprecated Devrait être déplacé dans UnicaenApp.
 */
class MessageContainer implements MessageAwareInterface
{
    use MessageAwareTrait;
}