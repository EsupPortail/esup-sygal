<?php

namespace Application\Event;

use UnicaenAuth\Event\Listener\UserRoleSelectedEventAbstractListener;
use UnicaenAuth\Event\UserRoleSelectedEvent;

class UserRoleSelectedEventListener extends UserRoleSelectedEventAbstractListener
{
    /**
     * @param UserRoleSelectedEvent $e
     */
    public function postSelection(UserRoleSelectedEvent $e)
    {
        // l'enregistrement du dernier rôle sélectionné est fait par unicaen/auth désormais
    }
}