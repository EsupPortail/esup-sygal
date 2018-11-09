<?php

namespace Soutenance\Form\Confidentialite;

use Application\Entity\Db\These;
use DateTime;
use Soutenance\Entity\Proposition;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ConfidentialiteHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $date = DateTime::createFromFormat("d/m/Y",$data['date']);
        $proposition->setConfidentialite($date);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        $data = [];
        $data['date']  = ($proposition->getConfidentialite())?$proposition->getConfidentialite()->format("d/m/Y"):"";

        return $data;
    }
}
