<?php

namespace ComiteSuiviIndividuel\Form\Membre;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;

class MembreHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var QualiteService $qualiteService
         */
        $qualiteService = $container->get(QualiteService::class);

        /** @var MembreForm $form */
        $hydrator = new MembreHydrator();
        $hydrator->setQualiteService($qualiteService);

        return $hydrator;
    }
}