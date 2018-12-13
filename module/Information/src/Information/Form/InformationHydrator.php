<?php

namespace Information\Form;


use Information\Entity\Db\Information;
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
            'priorite' => $object->getPriorite(),
            'visible' => $object->isVisible(),
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
        $object->setPriorite($data['priorite']);
        $object->setVisible( ($data['visible'] == 1)?true:false);
        return $object;
    }

}