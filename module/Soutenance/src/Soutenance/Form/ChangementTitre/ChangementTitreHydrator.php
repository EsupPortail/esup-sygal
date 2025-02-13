<?php

namespace Soutenance\Form\ChangementTitre;

use DateTime;
use Laminas\Hydrator\HydratorInterface;
use Soutenance\Entity\PropositionThese;

class ChangementTitreHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param PropositionThese $proposition
     * @return PropositionThese
     */
    public function hydrate(array $data, $proposition) {

        $proposition->setNouveauTitre($data['titre']);
        return $proposition;
    }

    /**
     * @param PropositionThese $proposition
     * @return array
     */
    public function extract($proposition): array {

        /** @var DateTime $datetime */
        $data['titre']      = ($proposition->getNouveauTitre())?:$proposition->getThese()->getTitre();

        return $data;
    }
}
