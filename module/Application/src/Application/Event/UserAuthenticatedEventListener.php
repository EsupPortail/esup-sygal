<?php

namespace Application\Event;

use Application\Entity\AuthUserWrapper;
use Application\Entity\Db\Utilisateur;
use UnicaenAuth\Event\Listener\AuthenticatedUserSavedAbstractListener;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use UnicaenAuth\Service\UserContext as UserContextService;

class UserAuthenticatedEventListener extends AuthenticatedUserSavedAbstractListener
{
    /**
     * @var UserContextService
     */
    private $userContextService;

    /**
     * @param UserContextService $userContextService
     */
    public function setAuthUserContextService(UserContextService $userContextService)
    {
        $this->userContextService = $userContextService;
    }

    /**
     * @param UserAuthenticatedEvent $e
     */
    public function onUserAuthenticatedPrePersist(UserAuthenticatedEvent $e)
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $e->getDbUser();

        // màj NOM Prénom
        switch (true) {
            case $user = $e->getLdapUser():
            case $user = $e->getShibUser():
            case $user = $e->getDbUser():
        }
        $userWrapper= AuthUserWrapper::inst($user);
        $utilisateur->setDisplayName($userWrapper->getDisplayName());

        // Sélection du dernier rôle endossé.
        if ($role = $utilisateur->getLastRole()) {
            $this->userContextService->setNextSelectedIdentityRole($role);
        }
    }
}