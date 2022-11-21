<?php

namespace Soutenance\Form\Justificatif;

use Depot\Service\FichierThese\FichierTheseServiceAwareTrait;
use Soutenance\Entity\Justificatif;
use Soutenance\Entity\Membre;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

/**
 * NB : Fichier est un object "complexe" géré en dehors de l'hydrator directement dans les actions ...
 */

class JusticatifHydrator implements HydratorInterface {
    use FichierTheseServiceAwareTrait;
    use MembreServiceAwareTrait;

    /**
     * NB : ne devrait pas servir ...
     * @param Justificatif $object
     * @return array
     */
    public function extract($object): array
    {
        $data  = [
            'nature' => ($object && $object->getFichier() &&  $object->getFichier()->getFichier()->getNature())? $object->getFichier()->getFichier()->getNature()->getCode(): null,
            'membre' => ($object && $object->getMembre())? $object->getMembre()->getId(): null,
//            'fichier' => null,
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Justificatif $object
     * @return Justificatif
     */
    public function hydrate(array $data, $object)
    {
        /** @var Membre $membre */
        $membre = $this->getMembreService()->find($data['membre']);
        $object->setMembre($membre);
        return $object;
    }

}