<?php

namespace Soutenance\Form\Justificatif;

use Application\Entity\Db\Fichier;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;

class JusticatifHydrator implements HydratorInterface {
    use FichierServiceAwareTrait;

    /**
     * NB : ne devrait pas servir ...
     * @param Fichier $object
     * @return array
     */
    public function extract($object)
    {
        $data  = [
            'nature' => $object->getNature()->getCode(),
            'fichier' => null,
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Fichier $object
     * @return Fichier
     */
    public function hydrate(array $data, $object)
    {
        $nature = $this->fichierService->fetchNatureFichier($data['nature']);
        $object->setNature($nature);
        return $object;
    }

}