<?php

namespace Soutenance\Form\Justificatif;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionService;

class JustificatifFormFactory
{

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var PropositionService $propositionService
         */
        $propositionService = $container->get(PropositionService::class);

        /** @var JusticatifHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(JusticatifHydrator::class);

        $form = new JustificatifForm();
        $form->setPropositionService($propositionService);
        $form->setHydrator($hydrator);
        return $form;
    }
}