<?php

namespace Soutenance\Form\Cotutelle;

use Application\Entity\Db\These;
use Zend\Stdlib\Hydrator\HydratorInterface;

class CotutelleHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param These $these
     * @return These
     */
    public function hydrate(array $data, $these) {

        $these->setLibelleEtabCotutelle($data['etablissement']);
        $these->setLibellePaysCotutelle($data['pays']);

        return $these;
    }

    /**
     * @param These $these
     * @return array
     */
    public function extract($these) {

        $data = [];
        $data['etablissement']  = $these->getLibelleEtabCotutelle();
        $data['pays']           = $these->getLibellePaysCotutelle();

        return $data;
    }
}
