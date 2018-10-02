<?php

namespace Indicateur\Form;

use Indicateur\Model\Indicateur;
use Zend\Stdlib\Hydrator\HydratorInterface;

class IndicateurHydrator implements HydratorInterface {

    /**
     * @param Indicateur $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'libelle'       => $object->getLibelle(),
            'description'   => $object->getDescription(),
            'requete'       => $object->getRequete(),
            'displayAs'     => $object->getDisplayAs(),
            'class'         => $object->getClass(),
            ];

        return $data;
    }

    /**
     * @param array $data
     * @param Indicateur $object
     * @return Indicateur
     */
    public function hydrate(array $data, $object)
    {
        $object->setLibelle( (isset($data['libelle']))?$data['libelle']:null);
        $object->setDescription( (isset($data['description']))?$data['description']:null);
        $object->setRequete( (isset($data['requete']))?$data['requete']:null);
        $object->setDisplayAs( (isset($data['displayAs']))?$data['displayAs']:null);
        $object->setClass( (isset($data['class']))?$data['class']:null);

        return $object;
    }

}