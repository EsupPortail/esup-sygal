<?php

namespace Structure\Form\Factory;

use Application\Entity\Db\Variable;
use Application\Service\Variable\VariableService;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;
use Structure\Form\VariableForm;
use Structure\Service\Etablissement\EtablissementService;
use These\Fieldset\TitreAcces\TitreAccesHydrator;

class VariableFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
//        /** @var ProfilHydrator $hydrator */
//        $hydrator = $container->get('HydratorManager')->get(ProfilHydrator::class);
        /** @var TitreAccesHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);

        /** @var VariableForm $form */
        $form = new VariableForm();
        $form->setHydrator($hydrator);

        /** @var VariableService $variableService */
        $variableService = $container->get(VariableService::class);
        $form->setObject($variableService->newVariable());

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $form->setEtablissementService($etablissementService);

        return $form;
    }
}