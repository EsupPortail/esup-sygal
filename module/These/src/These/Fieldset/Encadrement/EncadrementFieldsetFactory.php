<?php

namespace These\Fieldset\Encadrement;

use Application\View\Renderer\PhpRenderer;
use Individu\Entity\Db\Individu;
use Interop\Container\ContainerInterface;
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

        /** @var PhpRenderer $renderer*/
        $renderer = $container->get('ViewRenderer');
        /** @see IndividuController::rechercherAction() */
        $fieldset->setUrlCoEncadrant($renderer->url('individu/rechercher', [], [], true));
        $fieldset->setObject(new These());

        return $fieldset;
    }
}