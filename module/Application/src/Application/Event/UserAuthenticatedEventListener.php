<?php

namespace Application\Event;

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

        // NOM Prénom
        $utilisateur->setDisplayName($e->getLdapUser()->getNomComplet(true));

        // Sélection du dernier rôle endossé.
        if ($role = $utilisateur->getLastRole()) {
            $this->userContextService->setNextSelectedIdentityRole($role);
        }
    }
}