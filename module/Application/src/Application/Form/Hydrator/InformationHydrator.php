<?php

namespace Application\Form\Hydrator;


use Application\Entity\Db\Information;
use Zend\Stdlib\Hydrator\HydratorInterface;

class InformationHydrator implements HydratorInterface
{
    /**
     * @param Information $object
     * @return array
     */
    public function extract($object)
    {
        return [
            'titre' => $object->getTitre(),
            'contenu' => $object->getContenu(),
        ];
    }

    /**
     * @param array $data
     * @param Information $object
     * @return Information
     */
    public function hydrate(array $data, $object)
    {
        $object->setTitre($data['titre']);
        $object->setContenu($data['contenu']);
        return $object;
    }

}