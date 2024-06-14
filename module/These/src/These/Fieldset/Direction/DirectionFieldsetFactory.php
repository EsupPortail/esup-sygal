<?php

namespace These\Fieldset\Direction;

use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Service\Etablissement\EtablissementService;

class DirectionFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DirectionFieldset
    {
        $fieldset = new DirectionFieldset('Direction');

        /** @var DirectionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(DirectionHydrator::class);
        $fieldset->setHydrator($hydrator);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $fieldset->setQualiteService($qualiteService);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlAutocompleteIndividu($renderer->url('individu/rechercher', [], [], true)); //todo route de recherche des directeurs ?
        $fieldset->setUrlAutocompleteEtablissement($renderer->url('etablissement/rechercher', [], [], true));
        $fieldset->setUrlAutocompleteEcoleDoctorale($renderer->url('ecole-doctorale/rechercher', [], [], true));
        $fieldset->setUrlAutocompleteUniteRecherche($renderer->url('unite-recherche/rechercher', [], [], true));

        return $fieldset;
    }
}