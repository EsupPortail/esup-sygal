<?php

namespace These\Fieldset\Acteur;

use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Entity\Db\TypeStructure;
use Structure\Service\Structure\StructureService;

class ActeurFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ActeurFieldset
    {
        $fieldset = new ActeurFieldset();

        /** @var \Structure\Service\Structure\StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        /** @var \Structure\Entity\Db\UniteRecherche[] $urs */
        $urs = $structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE,
            ['structure.sigle', 'structure.libelle'],
            false
        );
        $fieldset->setUnitesRecherches($urs);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlIndividu($renderer->url('individu/rechercher', [], [], true));
        $fieldset->setUrlEtablissement($renderer->url('etablissement/rechercher', [], [], true));

        /** @var ActeurHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ActeurHydrator::class);
        $fieldset->setHydrator($hydrator);

        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $fieldset->setQualiteService($qualiteService);

        return $fieldset;
    }
}