<?php

namespace HDR\Fieldset\Generalites;

use Application\Service\VersionDiplome\VersionDiplomeService;
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

        /** @var VersionDiplomeService $versionDiplomeService */
        $versionDiplomeService = $container->get(VersionDiplomeService::class);
        $fieldset->setVersionDiplomeService($versionDiplomeService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $etablissements = $etablissementService->getRepository()->findAllEtablissementsInscriptions();
        $fieldset->setSelectableEtablissements($etablissements);

        /** @var GeneralitesHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(GeneralitesHydrator::class);
        $fieldset->setHydrator($hydrator);
        $fieldset->setObject(new HDR());

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlAutocompleteIndividu($renderer->url('individu/rechercher', [], [], true));
        $fieldset->setUrlAutocompleteEtablissement($renderer->url('etablissement/rechercher', [], [], true));

        return $fieldset;
    }
}