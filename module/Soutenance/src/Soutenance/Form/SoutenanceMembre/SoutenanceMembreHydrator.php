<?php

namespace Soutenance\Form\SoutenanceMembre;

use DateTime;
use Soutenance\Entity\Proposition;
use Zend\Stdlib\Hydrator\HydratorInterface;

class SoutenanceMembreHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

//        $date = DateTime::createFromFormat("d/m/Y H:i:s", $data['date'].' '.$data['heure']);
//        $proposition->setDate($date);
//        $proposition->setLieu($data['lieu']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        /** @var DateTime $datetime */
//        $datetime = $proposition->getDate();
//        $date = '';
//        $heure = '';
//        if ($datetime) {
//            $date = $datetime->format("d/m/Y");
//            $heure = $datetime->format("H:i:s");
//        }
//
        $data = [];
//        $data['date']       = $date;
//        $data['heure']      = $heure;
//        $data['lieu']       = $proposition->getLieu();

        return $data;
    }
}
