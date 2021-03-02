<?php

namespace Application\Service;

use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

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
        $authenticationService = $container->get('Zend\Authentication\AuthenticationService');

        /** @var IndividuService $individuService */
        $individuService = $container->get('IndividuService');

        $etablissementService = $this->locateEtablissementService($container);

        $service = new UserContextService();
        $service->setIndividuService($individuService);
        $service->setEtablissementService($etablissementService);
        $service->setAuthenticationService($authenticationService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}