<?php

namespace Application\Event;

use Application\Entity\UserWrapperFactory;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Individu\Service\IndividuServiceLocateTrait;
use Application\Service\Source\SourceService;
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

        /** @var UserWrapperFactory $userWrapperFactory */
        $userWrapperFactory = $container->get(UserWrapperFactory::class);

        $listener = new UserAuthenticatedEventListener();
        $listener->setServiceUserContext($userContextService);
        $listener->setIndividuService($individuService);
        $listener->setEtablissementService($etablissementService);
        $listener->setUtilisateurService($this->locateUtilisateurService($container));
        $listener->setUserWrapperFactory($userWrapperFactory);

        /**
         * @var \Application\Service\Source\SourceService $sourceService
         */
        $sourceService = $container->get(SourceService::class);
        $listener->setSourceService($sourceService);

        return $listener;
    }
}