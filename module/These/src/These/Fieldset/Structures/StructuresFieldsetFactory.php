<?php

namespace These\Fieldset\Structures;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use These\Entity\Db\These;

class StructuresFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructuresFieldset
    {
        $fieldset = new StructuresFieldset();

        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $fieldset->setStructureService($structureService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);
        $fieldset->setHydrator($hydrator);

        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fieldset->setEntityManager($entityManager);

        $fieldset->setObject(new These());

        return $fieldset;
    }
}