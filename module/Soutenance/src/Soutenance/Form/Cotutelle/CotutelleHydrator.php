<?php

namespace Soutenance\Form\Cotutelle;

use Application\Entity\Db\These;
use Soutenance\Entity\Proposition;
use Zend\Stdlib\Hydrator\HydratorInterface;

class CotutelleHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Proposition $proposition
     * @return Proposition
     */
    public function hydrate(array $data, $proposition) {

        $proposition->setEtablissementCotutel($data['etablissement']);
        $proposition->setPaysCotutel($data['pays']);

        return $proposition;
    }

    /**
     * @param Proposition $proposition
     * @return array
     */
    public function extract($proposition) {

        $data = [];
        $data['etablissement']  = $proposition->getEtablissementCotutel();
        $data['pays']           = $proposition->getPaysCotutel();

        return $data;
    }
}
