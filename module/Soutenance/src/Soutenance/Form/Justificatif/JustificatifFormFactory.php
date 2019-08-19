<?php

namespace Soutenance\Form\Justificatif;

use Soutenance\Service\Proposition\PropositionService;
use Zend\Form\FormElementManager;

class JustificatifFormFactory {

    public function __invoke(FormElementManager $container)
    {
        /**
         * @var JusticatifHydrator $hydrator
         */
        $hydrator = $container->getServiceLocator()->get('HydratorManager')->get(JusticatifHydrator::class);

        /**
         * @var PropositionService $propositionService
         */
        $propositionService = $container->getServiceLocator()->get(PropositionService::class);

        $form = new JustificatifForm();
        $form->setPropositionService($propositionService);
        $form->setHydrator($hydrator);
        $form->init();
        return $form;
    }
}