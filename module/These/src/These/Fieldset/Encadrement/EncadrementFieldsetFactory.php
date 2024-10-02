<?php

namespace These\Fieldset\Encadrement;

use Application\View\Renderer\PhpRenderer;
use Individu\Entity\Db\Individu;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Service\Etablissement\EtablissementService;
use These\Entity\Db\These;

class EncadrementFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EncadrementFieldset
    {
        $fieldset = new EncadrementFieldset();

        /** @var EncadrementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(EncadrementHydrator::class);
        $fieldset->setHydrator($hydrator);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $fieldset->setQualiteService($qualiteService);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        //todo route de recherche des directeurs
        /** @see IndividuController::rechercherAction() */
        $fieldset->setUrlDirecteur($renderer->url('individu/rechercher', [], [], true));
        /** @see IndividuController::rechercherAction() */
        $fieldset->setUrlCoEncadrant($renderer->url('individu/rechercher', [], ["query" => ['type' => Individu::TYPE_ACTEUR]], true));
        $fieldset->setObject(new These());

        return $fieldset;
    }
}