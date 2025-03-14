<?php

namespace HDR\Fieldset\Structures;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use HDR\Entity\Db\HDR;
use Interop\Container\ContainerInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;

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

        $unitesRecherche = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, ['structure.code', 'structure.libelle'], false);
        $fieldset->setUnitesRecherche($unitesRecherche);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);
        $fieldset->setHydrator($hydrator);

        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fieldset->setEntityManager($entityManager);

        $fieldset->setObject(new HDR());

        return $fieldset;
    }
}