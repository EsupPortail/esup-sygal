<?php

namespace Application\Form\IndividuCompl;

use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class IndividuComplFormFactory {

    public function __invoke(ContainerInterface $container) : IndividuComplForm
    {
        /* @var PhpRenderer $renderer  */
        $renderer = $container->get('ViewRenderer');
        $url = $renderer->url('utilisateur/rechercher-individu', [], [], true);

        /** @var IndividuComplHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(IndividuComplHydrator::class);

        $form = new IndividuComplForm();
        $form->setUrl($url);
        $form->setHydrator($hydrator);
        return $form;
    }
}