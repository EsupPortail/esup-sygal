<?php

namespace Soutenance\Form\Confidentialite;

use Application\Entity\Db\These;
use DateTime;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ConfidentialiteHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param These $these
     * @return These
     */
    public function hydrate(array $data, $these) {

        $date = DateTime::createFromFormat("d/m/Y",$data['date']);
        $these->setDateFinConfidentialite($date);

        return $these;
    }

    /**
     * @param These $these
     * @return array
     */
    public function extract($these) {

        $data = [];
        $data['date']  = ($these->getDateFinConfidentialite())?$these->getDateFinConfidentialite()->format("d/m/Y"):"";

        return $data;
    }
}
