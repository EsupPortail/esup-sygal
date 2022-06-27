<?php

namespace Formation\Form\Session;

use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url;

class SessionFormFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionForm
     */
    public function __invoke(ContainerInterface $container) : SessionForm
    {
        /** @var EtablissementService $etablissementService */
        /** @var StructureService $structureService */
        $etablissementService = $container->get(EtablissementService::class);
        $structureService = $container->get(StructureService::class);

        /** @var SessionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(SessionHydrator::class);

        $form = new SessionForm();
        /** @var Url $urlManager */
        $pluginManager = $container->get('ViewHelperManager');
        $urlManager = $pluginManager->get('Url');
        /** @see AgentController::rechercherAction() */
        $urlResponsable =  $urlManager->__invoke('utilisateur/rechercher-individu', [], [], true);
        $form->setUrlResponsable($urlResponsable);
        $form->setEtablissementService($etablissementService);
        $form->setStructureService($structureService);
        $form->setHydrator($hydrator);
        return $form;
    }
}