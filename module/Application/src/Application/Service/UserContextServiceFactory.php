<?php

namespace Application\Service;

use Application\Entity\UserWrapperFactory;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Options\ModuleOptions;
use Laminas\Authentication\AuthenticationService;

class UserContextServiceFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @param ContainerInterface $container
     * @return UserContextService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $container->get('Laminas\Authentication\AuthenticationService');

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);

        $etablissementService = $this->locateEtablissementService($container);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('unicaen-auth_module_options');

        /** @var UserWrapperFactory $userWrapperFactory */
        $userWrapperFactory = $container->get(UserWrapperFactory::class);

        $service = new UserContextService();
        $service->setIndividuService($individuService);
        $service->setEtablissementService($etablissementService);
        $service->setAuthenticationService($authenticationService);
        $service->setModuleOptions($moduleOptions);
        $service->setUserWrapperFactory($userWrapperFactory);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}