<?php

namespace Application\Event;

use Application\Entity\UserWrapper;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Utilisateur;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use UnicaenAuth\Event\Listener\AuthenticatedUserSavedAbstractListener;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use UnicaenAuth\Service\UserContext as UserContextService;

class UserAuthenticatedEventListener extends AuthenticatedUserSavedAbstractListener
{
    use IndividuServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

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
     * Méthode appelée juste avant que l'entité utilisateur soit persistée.
     *
     * @param UserAuthenticatedEvent $e
     */
    public function onUserAuthenticatedPrePersist(UserAuthenticatedEvent $e)
    {
        $userWrapper = UserWrapper::instFromUserAuthenticatedEvent($e);

        /** @var Utilisateur $utilisateur */
        $utilisateur = $e->getDbUser();
        $utilisateur->setDisplayName($userWrapper->getDisplayName()); // màj NOM Prénom

        // Sélection du dernier rôle endossé.
        if ($role = $utilisateur->getLastRole()) {
            $this->userContextService->setNextSelectedIdentityRole($role);
        }
    }

    /**
     * Méthode appelée juste après que l'entité utilisateur soit persistée.
     *
     * @param UserAuthenticatedEvent $e
     */
    public function onUserAuthenticatedPostPersist(UserAuthenticatedEvent $e)
    {
        $userWrapper = UserWrapper::instFromUserAuthenticatedEvent($e);

        $empId = $userWrapper->getSupannEmpId();
        $domaineEtab = $userWrapper->getDomainFromEppn();

        $etablissement = $this->etablissementService->getRepository()->findOneByDomaine($domaineEtab);

        // création de l'Individu si besoin
        $individu = $this->individuService->getRepository()->findOneByEmpIdAndEtab($empId, $etablissement);
        if (null === $individu) {
            $createur = $this->utilisateurService->getRepository()->fetchAppPseudoUser();
            $this->individuService->createFromUserWrapperAndEtab($userWrapper, $etablissement, $createur);
        }
    }
}