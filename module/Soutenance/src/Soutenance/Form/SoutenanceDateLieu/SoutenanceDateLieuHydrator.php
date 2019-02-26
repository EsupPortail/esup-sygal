<?php

namespace Soutenance\Form\SoutenanceDateLieu;

use DateTime;
use Soutenance\Entity\Proposition;
use Zend\Stdlib\Hydrator\HydratorInterface;

class SoutenanceDateLieuHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $date = DateTime::createFromFormat("d/m/Y H:i", $data['date'].' '.$data['heure']);
        $proposition->setDate($date);
        $proposition->setLieu($data['lieu']);
        $proposition->setExterieur($data['exterieur']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        /** @var DateTime $datetime */
        $datetime = $proposition->getDate();
        $date = '';
        $heure = '';
        if ($datetime) {
            $date = $datetime->format("d/m/Y");
            $heure = $datetime->format("H:i");
        }

        $data = [];
        $data['date']       = $date;
        $data['heure']      = $heure;
        $data['lieu']       = $proposition->getLieu();
        $data['exterieur']  = $proposition->isExterieur();

        return $data;
    }
}
