<?php

namespace Individu\Fieldset\IndividuRoleEtablissement;

use Individu\Entity\Db\IndividuRoleEtablissement;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class IndividuRoleEtablissementFieldsetFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuRoleEtablissementFieldset
    {
        $fieldset = new IndividuRoleEtablissementFieldset();

        /* @var PhpRenderer $renderer  */
        $renderer = $container->get('ViewRenderer');
        $fieldset->setUrlEtablissement($renderer->url('individu-role-etablissement/rechercher-etablissement'));

        /** @var IndividuRoleEtablissementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(IndividuRoleEtablissementHydrator::class);
        $fieldset->setHydrator($hydrator);
        $fieldset->setObject(new IndividuRoleEtablissement());

        return $fieldset;
    }
}