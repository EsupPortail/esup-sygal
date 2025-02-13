<?php

namespace HDR\Fieldset\Generalites;

use Application\Service\Discipline\DisciplineService;
use Application\View\Renderer\PhpRenderer;
use HDR\Entity\Db\HDR;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;

class GeneralitesFieldsetFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GeneralitesFieldset
    {
        $fieldset = new GeneralitesFieldset();

        /** @var DisciplineService $disciplineService */
        $disciplineService = $container->get(DisciplineService::class);
        $fieldset->setDisciplineService($disciplineService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        /** @var GeneralitesHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(GeneralitesHydrator::class);
        $fieldset->setHydrator($hydrator);
        $fieldset->setObject(new HDR());

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlAutocompleteIndividu($renderer->url('individu/rechercher', [], [], true));

        return $fieldset;
    }
}