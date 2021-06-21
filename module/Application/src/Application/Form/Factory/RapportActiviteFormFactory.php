<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RapportActiviteHydrator;
use Application\Form\RapportActiviteForm;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class RapportActiviteFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return RapportActiviteForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new RapportActiviteHydrator($em);

        $form = new RapportActiviteForm('rapport');
        $form->setHydrator($hydrator);

        return $form;
    }
}