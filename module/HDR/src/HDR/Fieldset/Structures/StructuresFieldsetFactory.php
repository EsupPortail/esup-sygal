<?php

namespace HDR\Fieldset\Structures;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Interop\Container\ContainerInterface;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use HDR\Entity\Db\HDR;

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

        $ecolesDoctorales = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);
        $fieldset->setEcolesDoctorales($ecolesDoctorales);
        $unitesRecherche = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, ['structure.sigle', 'structure.libelle'], false);
        $fieldset->setUnitesRecherche($unitesRecherche);
//        $etablissements = $etablissementService->getRepository()->findAllEtablissementsInscriptions();
//        $fieldset->setEtablissements($etablissements);

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