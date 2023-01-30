<?php

namespace RapportActivite\Form;

use RapportActivite\Hydrator\RapportActiviteHydrator;
use RapportActivite\Form\OldRapportActiviteForm;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class OldRapportActiviteFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return OldRapportActiviteForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $hydrator = new RapportActiviteHydrator($em);

        $form = new OldRapportActiviteForm('rapport');
        $form->setHydrator($hydrator);

        return $form;
    }
}