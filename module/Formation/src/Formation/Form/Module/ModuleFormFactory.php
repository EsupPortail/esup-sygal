<?php

namespace Formation\Form\Module;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;
use Zend\View\Helper\Url;

class ModuleFormFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleForm
     */
    public function __invoke(ContainerInterface $container) : ModuleForm
    {
        /** @var EtablissementService $etablissementService */
        /** @var StructureService $structureService */
        $etablissementService = $container->get(EtablissementService::class);
        $structureService = $container->get(StructureService::class);

        /** @var ModuleHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ModuleHydrator::class);

        $form = new ModuleForm();
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