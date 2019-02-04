<?php

namespace Application\Event;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactory;
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
        $userWrapperFactory = new UserWrapperFactory();
        $userWrapper = $userWrapperFactory->createInstanceFromUserAuthenticatedEvent($e);

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
     * Un Individu est créé/màj à partir de l'utilisateur qui vient de s'authentifier.
     *
     * @param UserAuthenticatedEvent $e
     */
    public function onUserAuthenticatedPostPersist(UserAuthenticatedEvent $e)
    {
        $userWrapperFactory = new UserWrapperFactory();
        $userWrapper = $userWrapperFactory->createInstanceFromUserAuthenticatedEvent($e);

        if ($userWrapper->getIndividu() !== null) {
            $individu = $userWrapper->getIndividu();
        } else {
            $individu = $this->processIndividu($userWrapper);
        }

        // renseigne le lien utilisateur-->individu
        /** @var Utilisateur $utilisateur */
        $utilisateur = $e->getDbUser();
        $this->utilisateurService->setIndividuForUtilisateur($individu, $utilisateur);
    }

    /**
     * @param UserWrapper $userWrapper
     * @return Individu
     */
    private function processIndividu(UserWrapper $userWrapper)
    {
        $createIndividu = false;
        $etablissementInconnu = $this->etablissementService->getRepository()->fetchEtablissementInconnu();

        // recherche de l'établissement de connexion l'utilisateur : à partir du domaine de l'EPPN, ex: 'unicaen.fr'
        $domaineEtab = $userWrapper->getDomainFromEppn();
        $etablissement = $this->etablissementService->getRepository()->findOneByDomaine($domaineEtab);

        if ($etablissement === null) {
            // si aucun établissement ne correspond au domaine, on essaie l'établissement "inconnu"...

            // recherche de l'Individu correspondant à l'utilisateur, peut-être rattaché à l'établissement inconnu?
            $individu = $this->individuService->getRepository()->findOneByUserWrapperAndEtab($userWrapper, $etablissementInconnu);
            if ($individu === null) {
                // si l'individu n'est pas trouvé dans l'établissement inconnu, il y sera ajouté.
                $createIndividu = true;
                $etablissement = $etablissementInconnu;
            }
        } else {
            // recherche de l'Individu dans l'établissement de connexion existant
            $individu = $this->individuService->getRepository()->findOneByUserWrapperAndEtab($userWrapper, $etablissement);

            if ($individu === null) {
                // si l'individu n'est pas trouvé dans l'établissement de connexion, recherche dans l'établissement inconnu
                $individu = $this->individuService->getRepository()->findOneByUserWrapperAndEtab($userWrapper, $etablissementInconnu);
                if ($individu === null) {
                    // si l'individu n'est pas trouvé non plus dans l'établissement inconnu, il sera ajouté dans l'établissement de connexion.
                    $createIndividu = true;
                } else {
                    // s'il existe dans l'établissement inconnu, on le "déplace" dans l'établissement de connexion
                    $this->individuService->updateIndividuSourceCodeFromEtab($individu, $etablissement);
                }
            }
        }

        // création de l'Individu si besoin
        if ($createIndividu) {
            $individu = $this->individuService->createIndividuFromUserWrapperAndEtab($userWrapper, $etablissement);
        }

        return $individu;
    }
}