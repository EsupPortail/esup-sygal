<?php

namespace These\Form\TheseSaisie;

use Application\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;

class TheseSaisieFormFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : TheseSaisieForm
    {
        /** @var TheseSaisieHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(TheseSaisieHydrator::class);
//        $hydrator = $container->get('HydratorManager')->get(DoctrineObject::class);

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');

        $form = new TheseSaisieForm();
        $form->setHydrator($hydrator);
//        $form->setObject(new These());

        return $form;
    }
}