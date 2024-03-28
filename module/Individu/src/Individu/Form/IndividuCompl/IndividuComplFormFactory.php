<?php

namespace Individu\Form\IndividuCompl;

use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class IndividuComplFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuComplForm
    {
        /* @var PhpRenderer $renderer  */
        $renderer = $container->get('ViewRenderer');
        $urlIndividu = $renderer->url('individu/rechercher', [], [], true);

        /** @var IndividuComplHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(IndividuComplHydrator::class);

        $form = new IndividuComplForm();
        $form->setUrlIndividu($urlIndividu);
        $form->setHydrator($hydrator);

        return $form;
    }
}