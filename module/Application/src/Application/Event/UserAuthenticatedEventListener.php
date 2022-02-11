<?php

namespace Application\Event;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactoryAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use UnicaenAuth\Event\Listener\AuthenticatedUserSavedAbstractListener;
use UnicaenAuth\Event\UserAuthenticatedEvent;

class UserAuthenticatedEventListener extends AuthenticatedUserSavedAbstractListener
{
    use IndividuServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use SourceServiceAwareTrait;
    use UserWrapperFactoryAwareTrait;

    /**
     * Méthode appelée juste avant que l'entité utilisateur soit persistée.
     *
     * @param UserAuthenticatedEvent $e
     */
    public function onUserAuthenticatedPrePersist(UserAuthenticatedEvent $e)
    {
        parent::onUserAuthenticatedPrePersist($e);

        try {
            $userWrapper = $this->userWrapperFactory->createInstanceFromUserAuthenticatedEvent($e);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return;
        }

        /** @var Utilisateur $utilisateur */
        $utilisateur = $e->getDbUser();
        $utilisateur->setDisplayName($userWrapper->getDisplayName()); // màj NOM Prénom
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
        parent::onUserAuthenticatedPostPersist($e);

        try {
            $userWrapper = $this->userWrapperFactory->createInstanceFromUserAuthenticatedEvent($e);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return;
        }

        if ($userWrapper->getIndividu() !== null) {
            $individu = $userWrapper->getIndividu();
        } else {
            $individu = $this->processIndividu($userWrapper);
        }

        // renseigne le lien utilisateur-->individu
        /** @var Utilisateur $utilisateur */
        $utilisateur = $e->getDbUser();
        if ($individu !== null) {
            $this->utilisateurService->setIndividuForUtilisateur($individu, $utilisateur);
        }
    }

    /**
     * @param UserWrapper $userWrapper
     * @return Individu|null
     */
    private function processIndividu(UserWrapper $userWrapper): ?Individu
    {
        $etablissementInconnu = $this->etablissementService->getRepository()->fetchEtablissementInconnu();

        // recherche de l'établissement de connexion l'utilisateur : à partir du domaine de l'EPPN, ex: 'unicaen.fr'
        $etablissement = null;
        if ($domaineEtab = $userWrapper->getDomainFromEppn()) {
            $etablissement = $this->etablissementService->getRepository()->findOneByDomaine($domaineEtab);
        }

        if ($etablissement === null) {
            // si aucun établissement ne correspond au domaine, on essaie l'établissement "inconnu"...

            // recherche de l'Individu correspondant à l'utilisateur, peut-être rattaché à l'établissement inconnu ?
            $individu = $this->individuService->getRepository()->findOneByUserWrapperAndEtab($userWrapper, $etablissementInconnu);
        } else {
            // recherche de l'Individu dans l'établissement de connexion existant
            $individu = $this->individuService->getRepository()->findOneByUserWrapperAndEtab($userWrapper, $etablissement);

            if ($individu === null) {
                // si l'individu n'est pas trouvé dans l'établissement de connexion, recherche dans l'établissement inconnu
                $individu = $this->individuService->getRepository()->findOneByUserWrapperAndEtab($userWrapper, $etablissementInconnu);
                if ($individu !== null) {
                    // s'il existe dans l'établissement inconnu, on le "déplace" dans l'établissement de connexion
                    $this->individuService->updateIndividuSourceCodeFromEtab(
                        $individu,
                        $etablissement,
                        $this->utilisateurService->fetchAppPseudoUtilisateur());
                }
            }
        }

        return $individu;
    }
}