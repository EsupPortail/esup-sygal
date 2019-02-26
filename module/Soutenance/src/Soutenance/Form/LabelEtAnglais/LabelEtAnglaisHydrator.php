<?php

namespace Soutenance\Form\LabelEtAnglais;

use Soutenance\Entity\Proposition;
use Zend\Stdlib\Hydrator\HydratorInterface;

class LabelEtAnglaisHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $proposition->setLabelEuropeen($data['label']);
        $proposition->setManuscritAnglais($data['manuscrit']);
        $proposition->setSoutenanceAnglais($data['soutenance']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        $data = [
            'label' => $proposition->isLabelEuropeen(),
            'manuscrit' => $proposition->isManuscritAnglais(),
            'soutenance' => $proposition->isSoutenanceAnglais(),
        ];


        return $data;
    }
}
