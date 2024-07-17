<?php

namespace These\Fieldset\Generalites;

use Application\Service\Discipline\DisciplineService;
use Application\Service\Pays\PaysService;
use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use These\Entity\Db\These;

class GeneralitesFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
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
        $fieldset->setObject(new These());

        /** @var PaysService $paysService */
        $paysService = $container->get(PaysService::class);
        $pays = $paysService->getPaysAsOptions();
        $fieldset->setPays($pays);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlAutocompleteIndividu($renderer->url('individu/rechercher', [], [], true));

        return $fieldset;
    }
}