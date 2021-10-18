<?php

namespace Soutenance\Form\Confidentialite;

use DateTime;
use Soutenance\Entity\Proposition;
use Laminas\Hydrator\HydratorInterface;

class ConfidentialiteHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $date = null;
        $huisclos = false;
        if ($data['date'] and $proposition->getThese()->getDateFinConfidentialite() === null) {
            $date = DateTime::createFromFormat("Y-m-d", $data['date']);
        }
        if ($data['date'] OR $proposition->getThese()->getDateFinConfidentialite() !== null) {
            $huisclos = $data['huitclos']??false;
        }

        $proposition->setConfidentialite($date);
        $proposition->setHuitClos($huisclos);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        $data = [];
        $data['date']  = ($proposition->getConfidentialite())?$proposition->getConfidentialite()->format("Y-m-d"):"";
        $data['huitclos'] = $proposition->isHuitClos();

        return $data;
    }
}
