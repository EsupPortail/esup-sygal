<?php

namespace Soutenance\Form\SoutenanceDateRenduRapport;

use DateTime;
use Soutenance\Entity\Proposition;
use Zend\Stdlib\Hydrator\HydratorInterface;

class SoutenanceDateRenduRapportHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $date = DateTime::createFromFormat("d/m/Y", $data['date']);
        $proposition->setRenduRapport($date);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        $data = [];
        $data['date']       = $proposition->getRenduRapport();


        return $data;
    }
}
