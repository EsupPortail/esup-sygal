<?php

namespace Soutenance\Form\QualiteEdition;

use Laminas\Hydrator\HydratorInterface;
use Soutenance\Entity\Qualite;

class QualiteEditiontHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param Qualite $qualite
     * @return Qualite
     */
    public function hydrate(array $data, $qualite): Qualite
    {
        $data['rang'] = $data['rang'] ?? null;

        $qualite->setRang($data['rang'] === 'aucun' ? null : $data['rang']);
        $qualite->setLibelle($data['libelle']);
        $qualite->setHdr($data['hdr']);
        $qualite->setEmeritat($data['emeritat']);
        $qualite->setJustificatif($data['justificatif']);

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return array
     */
    public function extract($qualite): array
    {
        $data = [];
        $data['libelle']         = $qualite->getLibelle();
        $data['rang']            = $qualite->getRang();
        $data['hdr']             = $qualite->getHdr();
        $data['emeritat']        = $qualite->getEmeritat();
        $data['justificatif']    = $qualite->getJustificatif();

        return $data;
    }
}
