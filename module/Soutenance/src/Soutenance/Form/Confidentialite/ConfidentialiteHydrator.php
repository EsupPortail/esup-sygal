<?php

namespace Soutenance\Form\Confidentialite;

use DateTime;
use Soutenance\Entity\Proposition;
use Zend\Hydrator\HydratorInterface;

class ConfidentialiteHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        if ($data['date']) {
            $date = DateTime::createFromFormat("Y-m-d", $data['date']);
            $proposition->setConfidentialite($date);
            $proposition->setHuitClos($data['huitclos']);
        } else {
            $proposition->setConfidentialite(null);
            $proposition->setHuitClos(false);
        }

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
