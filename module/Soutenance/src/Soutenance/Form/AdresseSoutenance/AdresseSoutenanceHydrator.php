<?php

namespace Soutenance\Form\AdresseSoutenance;

use Soutenance\Entity\Proposition;
use Laminas\Hydrator\HydratorInterface;

class AdresseSoutenanceHydrator implements HydratorInterface {

    /**
     * @param Proposition $object
     * @return array
     */
    public function extract($object): array
    {
        $data = [
          'adresse' => $object->getAdresse(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param Proposition $object
     * @return Proposition
     */
    public function hydrate(array $data, $object)
    {
        if (!isset($data['adresse']) OR $data['adresse'] == '') $object->setAdresse(null);
        else {
            $adresse = $data['adresse'];
            $adresse = str_replace("</p>", "<br/>", $adresse);
            $adresse = str_replace("<p>", "", $adresse);
            $object->setAdresse($adresse);
        }
        return $object;
    }


}