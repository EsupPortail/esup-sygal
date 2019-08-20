<?php

namespace Soutenance\Form\QualiteEdition;

use Soutenance\Entity\Qualite;
use Zend\Stdlib\Hydrator\HydratorInterface;

class QualiteEditiontHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Qualite $qualite
     * @return Qualite
     */
    public function hydrate(array $data, $qualite) {

        $qualite->setRang($data['rang']);
        $qualite->setLibelle($data['libelle']);
        $qualite->setHdr($data['hdr']);
        $qualite->setEmeritat($data['emeritat']);

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return array
     */
    public function extract($qualite) {

        $data = [];
        $data['libelle']    = $qualite->getLibelle();
        $data['rang']       = $qualite->getRang();
        $data['hdr']        = $qualite->getHdr();
        $data['emeritat']        = $qualite->getEmeritat();

        return $data;
    }
}
