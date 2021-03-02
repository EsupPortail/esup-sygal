<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use Zend\Hydrator\HydratorInterface;

class ComiteSuiviHydrator implements HydratorInterface {
    use TheseServiceAwareTrait;

    /**
     * @param ComiteSuivi $object
     * @return array
     */
    public function extract($object)
    {
        $data = [
            'these' => ($object->getThese())?$object->getThese()->getId():null,
            'annee_these' => $object->getAnneeThese(),
            'annee_scolaire' => $object->getAnneeScolaire(),
        ];
        return $data;
    }

    /**
     * @param array $data
     * @param ComiteSuivi $object
     * @return ComiteSuivi
     */
    public function hydrate(array $data, $object)
    {
        /** @var These $these */
        $these = isset($data['these'])?$this->getTheseService()->getRepository()->find($data['these']):null;

        $object->setThese($these);
        $object->setAnneeThese(isset($data['annee_these'])?$data['annee_these']:null);
        $object->setAnneeScolaire(isset($data['annee_scolaire'])?$data['annee_scolaire']:null);

        return $object;
    }


}