<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Service\Membre\MembreService;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use Zend\Form\FormElementManager;

class CompteRenduFormFactory {
    use MembreServiceAwareTrait;

    public function __invoke(FormElementManager $manager)
    {
        /**
         * @var MembreService $membreService
         */
        $membreService = $manager->getServiceLocator()->get(MembreService::class);

        /** @var CompteRenduHydrator $hydrator */
        $hydrator = $manager->getServiceLocator()->get('HydratorManager')->get(CompteRenduHydrator::class);

        /** @var CompteRenduForm $form */
        $form = new CompteRenduForm();
        $form->setMembreService($membreService);
        $form->setHydrator($hydrator);
        return $form;
    }
}