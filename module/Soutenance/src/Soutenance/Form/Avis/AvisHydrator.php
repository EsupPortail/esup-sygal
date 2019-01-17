<?php

namespace Soutenance\Form\Avis;

use Soutenance\Entity\Avis;
use Zend\Stdlib\Hydrator\HydratorInterface;

class AvisHydrator implements HydratorInterface {

    /**
     * @param Avis $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'avis' => $object->getAvis(),
            'motifs' => $object->getMotif()
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Avis $object
     * @return Avis
     */
    public function hydrate(array $data, $object)
    {
        $object->setAvis($data['avis']);
        $object->setMotif($data['motif']);
        return $object;
    }

}