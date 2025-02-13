<?php

namespace Soutenance\Form\LabelEuropeen;

use Laminas\Hydrator\HydratorInterface;
use Soutenance\Entity\PropositionThese;

class LabelEuropeenHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param PropositionThese $proposition
     * @return PropositionThese
     */
    public function hydrate(array $data, $proposition) {

        $proposition->setLabelEuropeen($data['label']);

        return $proposition;
    }

    /**
     * @param PropositionThese $proposition
     * @return array
     */
    public function extract($proposition): array {

        $data = [
            'label' => $proposition->isLabelEuropeen(),
        ];


        return $data;
    }
}
