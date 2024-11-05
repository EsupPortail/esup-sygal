<?php

namespace Structure\Form\Factory;

use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Structure\Form\Hydrator\VariableHydrator;
use Structure\Form\VariableForm;
use Structure\Service\Etablissement\EtablissementService;

class VariableFormFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): VariableForm
    {
        $hydrator = $container->get('HydratorManager')->get(VariableHydrator::class);

        $form = new VariableForm();
        $form->setHydrator($hydrator);

        /** @var VariableService $variableService */
        $variableService = $container->get(VariableService::class);
        $form->setVariableService($variableService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $form->setEtablissementService($etablissementService);

        return $form;
    }
}