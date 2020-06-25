<?php

namespace Soutenance\Form\QualiteLibelleSupplementaire;

use Interop\Container\ContainerInterface;

class QualiteLibelleSupplementaireFormFactory
{

    /**
     * @param ContainerInterface $container
     * @return QualiteLibelleSupplementaireForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var QualiteLibelleSupplementaireHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(QualiteLibelleSupplementaireHydrator::class);

        /** @var QualiteLibelleSupplementaireForm $form */
        $form = new QualiteLibelleSupplementaireForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}