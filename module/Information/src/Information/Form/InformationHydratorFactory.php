<?php

namespace Information\Form;

use Information\Service\InformationLangue\InformationLangueService;
use Interop\Container\ContainerInterface;

class InformationHydratorFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var InformationLangueService $informationLangueService
         */
        $informationLangueService = $container->get(InformationLangueService::class);

        $hydrator = new InformationHydrator();
        $hydrator->setInformationLangueService($informationLangueService);
        return $hydrator;
    }
}