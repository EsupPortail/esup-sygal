<?php

namespace Application\Form\IndividuCompl;

use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class IndividuComplFormFactory {

    public function __invoke(ContainerInterface $container) : IndividuComplForm
    {
        /* @var PhpRenderer $renderer  */
        $renderer = $container->get('ViewRenderer');
        $urlIndividu = $renderer->url('individu/rechercher', [], [], true);
        $urlEtablissement = $renderer->url('etablissement/rechercher', [], [], true);
        $urlUnite = $renderer->url('unite-recherche/rechercher', [], [], true);

        /** @var IndividuComplHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(IndividuComplHydrator::class);

        $form = new IndividuComplForm();
        $form->setUrlIndividu($urlIndividu);
        $form->setUrlEtablissement($urlEtablissement);
        $form->setUrlUniteRecherche($urlUnite);
        $form->setHydrator($hydrator);
        return $form;
    }
}