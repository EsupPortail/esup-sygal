<?php

namespace These\Fieldset\Generalites;

use Application\Service\Discipline\DisciplineService;
use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class GeneralitesFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GeneralitesFieldset
    {
        $fieldset = new GeneralitesFieldset('Generalites');

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        /** @var DisciplineService $disciplineService */
        $disciplineService = $container->get(DisciplineService::class);
        $fieldset->setDisciplineService($disciplineService);

        /** @var GeneralitesHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(GeneralitesHydrator::class);
        $fieldset->setHydrator($hydrator);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlDoctorant($renderer->url('recherche-doctorant', [], [], true));

        return $fieldset;
    }
}