<?php

namespace These\Form\DomaineHalSaisie\Fieldset;

use Application\Service\DomaineHal\DomaineHalService;
use Interop\Container\ContainerInterface;
use These\Entity\Db\These;

class DomaineHalFieldsetFactory
{

    public function __invoke(ContainerInterface $container): DomaineHalFieldset
    {

        /** @var DomaineHalHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DomaineHalHydrator::class);
        $fieldset = new DomaineHalFieldset();
        $fieldset->setObject(new These());
        $fieldset->setHydrator($hydrator);

        /** @var DomaineHalService $domaineHalService */
        $domaineHalService = $container->get(DomaineHalService::class);
        $domainesHal = $domaineHalService->getDomainesHalAsOptions();
        $fieldset->setDomainesHal($domainesHal);
        return $fieldset;
    }
}