<?php

namespace Soutenance\Form\DateLieu;

use DateTime;
use Soutenance\Entity\Proposition;
use Laminas\Hydrator\HydratorInterface;

class DateLieuHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $date = DateTime::createFromFormat("Y-m-d H:i", $data['date'].' '.$data['heure']);
        $proposition->setDate($date);
        $proposition->setLieu($data['lieu']);
        $proposition->setExterieur($data['exterieur']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition): array {

        /** @var DateTime $datetime */
        $datetime = $proposition->getDate();
        $date = '';
        $heure = '';
        if ($datetime) {
            $date = $datetime->format("Y-m-d");
            $heure = $datetime->format("H:i");
        }

        $data = [];
        $data['date']       = $date;
        $data['heure']      = $heure;
        $data['lieu']       = $proposition->getLieu();
        $data['exterieur']  = ($proposition->isExterieur())?1:0;
        return $data;
    }
}
