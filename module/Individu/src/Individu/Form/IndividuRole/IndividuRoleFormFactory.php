<?php

namespace Individu\Form\IndividuRole;

use Individu\Entity\Db\IndividuRole;
use Psr\Container\ContainerInterface;

class IndividuRoleFormFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuRoleForm
    {
        $form = new IndividuRoleForm();

        /** @var IndividuRoleHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(IndividuRoleHydrator::class);
        $form->setHydrator($hydrator);
        $form->setObject(new IndividuRole());

        return $form;
    }
}