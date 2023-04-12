<?php

namespace UnicaenAvis\Fieldset;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Hydrator\AvisTypeHydrator;

class AvisTypeFieldsetFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisTypeFieldset
    {
        /** @var \UnicaenAvis\Hydrator\AvisTypeHydrator $avisTypeHydrator */
        $avisTypeHydrator = $container->get('HydratorManager')->get(AvisTypeHydrator::class);

        $fieldset = new AvisTypeFieldset();
        $fieldset->setHydrator($avisTypeHydrator);
        $fieldset->setObject(new AvisType());

        return $fieldset;
    }
}