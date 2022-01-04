<?php

namespace Soutenance\Form\LabelEuropeen;

use Soutenance\Entity\Proposition;
use Laminas\Hydrator\HydratorInterface;

class LabelEuropeenHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $proposition->setLabelEuropeen($data['label']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition): array {

        $data = [
            'label' => $proposition->isLabelEuropeen(),
        ];


        return $data;
    }
}
