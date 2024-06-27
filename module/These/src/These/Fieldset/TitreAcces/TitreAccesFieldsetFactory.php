<?php

namespace These\Fieldset\TitreAcces;

use Application\Entity\Db\TitreAcces;
use Interop\Container\ContainerInterface;
use These\Entity\Db\These;

class TitreAccesFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TitreAccesFieldset
    {
        $fieldset = new TitreAccesFieldset('AnneeUnivInscription');

        /** @var TitreAccesHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(TitreAccesHydrator::class);
        $fieldset->setHydrator($hydrator);

        $fieldset->setObject(new These());

        return $fieldset;
    }
}