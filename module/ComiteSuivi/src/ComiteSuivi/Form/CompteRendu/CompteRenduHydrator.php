<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Entity\Db\CompteRendu;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use Zend\Hydrator\HydratorInterface;

class CompteRenduHydrator implements HydratorInterface {
    use MembreServiceAwareTrait;

    /**
     * @param CompteRendu $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'examinateur' => ($object->getMembre())?$object->getMembre()->getId():null,
//            'reponse' => $object->getReponse(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param CompteRendu $object
     * @return CompteRendu
     */
    public function hydrate(array $data, $object)
    {
        $membre = isset($data['examinateur'])?$this->getMembreService()->getMembre($data['examinateur']):null;

        $object->setMembre($membre);
//        $object->setReponse($data['reponse']);

        return $object;
    }


}