<?php

namespace Soutenance\Form\Anglais;

use Soutenance\Entity\Proposition;
use Laminas\Hydrator\HydratorInterface;

class AnglaisHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

//        $proposition->setManuscritAnglais($data['manuscrit']);
        $proposition->setSoutenanceAnglais($data['soutenance']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        $data = [
//            'manuscrit' => $proposition->isManuscritAnglais(),
            'soutenance' => $proposition->isSoutenanceAnglais(),
        ];

        return $data;
    }
}
