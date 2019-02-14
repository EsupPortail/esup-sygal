<?php

namespace Application\Service;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserContextServiceFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @param ServiceLocatorInterface $sl
     * @return UserContextService
     */
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var IndividuService $individuService */
        $individuService = $sl->get('IndividuService');

        $etablissementService = $this->locateEtablissementService($sl);

        $service = new UserContextService();
        $service->setIndividuService($individuService);
        $service->setEtablissementService($etablissementService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}