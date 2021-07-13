<?php

namespace Soutenance\Form\DateRenduRapport;

use DateTime;
use Soutenance\Entity\Proposition;
use Laminas\Hydrator\HydratorInterface;

class DateRenduRapportHydrator implements HydratorInterface
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
    public function extract($proposition): array {

        $data = [];
        $data['date']       = $proposition->getRenduRapport();

        return $data;
    }
}
