<?php

namespace Soutenance\Form\Avis;

use Soutenance\Entity\Avis;
use Laminas\Hydrator\HydratorInterface;

class AvisHydrator implements HydratorInterface {

    /**
     * @param Avis $object
     * @return array
     */
    public function extract($object): array
    {
        return [
            'avis' => $object->getAvis(),
            'motifs' => $object->getMotif(),
            //'rapport' => $object->getFichier(),
        ];
    }

    /**
     * @param array $data
     * @param Avis $object
     * @return Avis
     */
    public function hydrate(array $data, $object)
    {
        $fichier = null;
        $object->setAvis($data['avis']);
        $object->setMotif($data['motif']);
        $object->setFichier($fichier);
        return $object;
    }

}