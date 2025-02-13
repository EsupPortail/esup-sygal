<?php

namespace HDR\Fieldset\Direction;

use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use HDR\Entity\Db\HDR;

class DirectionFieldsetFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DirectionFieldset
    {
        $fieldset = new DirectionFieldset();

        /** @var DirectionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DirectionHydrator::class);
        $fieldset->setHydrator($hydrator);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $fieldset->setQualiteService($qualiteService);

        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $fieldset->setStructureService($structureService);

        $ecolesDoctorales = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'structure.libelle', false);
        $fieldset->setEcolesDoctorales($ecolesDoctorales);
        $unitesRecherche = $structureService->findAllStructuresAffichablesByType(TypeStructure::CODE_UNITE_RECHERCHE, ['structure.sigle', 'structure.libelle'], false);
        $fieldset->setUnitesRecherche($unitesRecherche);
        $etablissements = $etablissementService->getRepository()->findAllEtablissementsInscriptions();
        $fieldset->setEtablissements($etablissements);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlAutocompleteIndividu($renderer->url('individu/rechercher', [], [], true)); //todo route de recherche des directeurs ?

        $fieldset->setObject(new HDR());
        return $fieldset;
    }
}