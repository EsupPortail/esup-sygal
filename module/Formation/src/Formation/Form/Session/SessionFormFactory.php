<?php

namespace Formation\Form\Session;

use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;

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
        $form->setEtablissementService($etablissementService);
        $form->setStructureService($structureService);
        $form->setHydrator($hydrator);

        return $form;
    }
}