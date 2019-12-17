<?php

namespace Soutenance\Form\QualiteLibelleSupplementaire;

use Zend\Form\FormElementManager;

class QualiteLibelleSupplementaireFormFactory {

    /**
     * @param FormElementManager $manager
     * @return QualiteLibelleSupplementaireForm
     */
    public function __invoke(FormElementManager $manager)
    {
        $sl = $manager->getServiceLocator();
        $hydrator = $sl->get('HydratorManager')->get(QualiteLibelleSupplementaireHydrator::class);

        /** @var QualiteLibelleSupplementaireForm $form */
        $form = new QualiteLibelleSupplementaireForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}