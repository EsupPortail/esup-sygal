<?php

namespace Application\Event;

use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurServiceLocateTrait;
use Interop\Container\ContainerInterface;

class UserAuthenticatedEventListenerFactory
{
    use IndividuServiceLocateTrait;
    use EtablissementServiceLocateTrait;
    use UtilisateurServiceLocateTrait;

    public function __invoke(ContainerInterface $container): UserAuthenticatedEventListener
    {
        /** @var UserContextService $userContextService */
        $userContextService = $container->get('AuthUserContext');
        $individuService = $this->locateIndividuService($container);
        $etablissementService = $this->locateEtablissementService($container);

        $listener = new UserAuthenticatedEventListener();
        $listener->setServiceUserContext($userContextService);
        $listener->setIndividuService($individuService);
        $listener->setEtablissementService($etablissementService);
        $listener->setUtilisateurService($this->locateUtilisateurService($container));

        return $listener;
    }
}