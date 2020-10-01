<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RapportAnnuelHydrator;
use Application\Form\RapportAnnuelForm;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class RapportAnnuelFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RapportAnnuelForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new RapportAnnuelHydrator($em);

        $form = new RapportAnnuelForm('rapportAnnuel');
        $form->setHydrator($hydrator);

        return $form;
    }
}