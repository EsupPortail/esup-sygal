<?php

namespace Application\Event;

use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurServiceLocateTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserAuthenticatedEventListenerFactory
{
    use IndividuServiceLocateTrait;
    use EtablissementServiceLocateTrait;
    use UtilisateurServiceLocateTrait;

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var UserContextService $userContextService */
        $userContextService = $serviceLocator->get('AuthUserContext');
        $individuService = $this->locateIndividuService($serviceLocator);
        $etablissementService = $this->locateEtablissementService($serviceLocator);

        $listener = new UserAuthenticatedEventListener();
        $listener->setAuthUserContextService($userContextService);
        $listener->setIndividuService($individuService);
        $listener->setEtablissementService($etablissementService);
        $listener->setUtilisateurService($this->locateUtilisateurService($serviceLocator));

        return $listener;
    }
}