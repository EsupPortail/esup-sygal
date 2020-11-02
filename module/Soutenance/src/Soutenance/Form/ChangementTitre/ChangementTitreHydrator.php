<?php

namespace Soutenance\Form\ChangementTitre;

use DateTime;
use Soutenance\Entity\Proposition;
use Zend\Hydrator\HydratorInterface;

class ChangementTitreHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $proposition->setNouveauTitre($data['titre']);
        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        /** @var DateTime $datetime */
        $data['titre']      = ($proposition->getNouveauTitre())?:$proposition->getThese()->getTitre();

        return $data;
    }
}
