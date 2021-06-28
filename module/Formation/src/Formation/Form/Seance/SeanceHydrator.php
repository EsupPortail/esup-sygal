<?php

namespace Formation\Form\Seance;

use DateTime;
use Formation\Entity\Db\Seance;
use Zend\Hydrator\HydratorInterface;

class SeanceHydrator implements HydratorInterface {

    /**
     * @param Seance $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'date'          => ($object->getDebut() !== null)?$object->getDebut()->format('d/m/Y'):null,
            'debut'         => ($object->getDebut() !== null)?$object->getDebut()->format('H:i'):null,
            'fin'           => ($object->getFin() !== null)?$object->getFin()->format('H:i'):null,
            'lieu'          => ($object->getLieu() !== null)?$object->getLieu():null,
            'description'   => ($object->getDescription() !== null)?$object->getDescription():null,
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Seance $object
     * @return Seance
     */
    public function hydrate(array $data, $object)
    {
        $date           = (isset($data['date']))?trim($data['date']):null;
        $debut          = (isset($data['debut']))?trim($data['debut']):null;
        $fin            = (isset($data['fin']))?trim($data['fin']):null;
        $lieu           = (isset($data['lieu']) AND trim($data['lieu']) !== '')?trim($data['lieu']):null;
        $description    = (isset($data['description']) AND trim($data['description']) !== '')?trim($data['description']):null;

        $date_debut = ($date AND $debut)? DateTime::createFromFormat('d/m/Y H:i', $date .' '.$debut):null;
        $date_fin   = ($date AND $debut)? DateTime::createFromFormat('d/m/Y H:i', $date .' '.$fin):null;

        $object->setDebut($date_debut);
        $object->setFin($date_fin);
        $object->setLieu($lieu);
        $object->setDescription($description);
        return $object;
    }

}