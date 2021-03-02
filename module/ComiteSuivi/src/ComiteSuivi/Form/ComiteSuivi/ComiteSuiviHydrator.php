<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use Application\Entity\Db\These;
use Application\Service\These\TheseServiceAwareTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use DateTime;
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
            'date_comite' => $object->getDateComite(),
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
        $dateComite = isset($data['date_comite'])?DateTime::createFromFormat('d/m/Y', $data['date_comite']):null;

        $object->setThese($these);
        $object->setAnneeThese(isset($data['annee_these'])?$data['annee_these']:null);
        $object->setAnneeScolaire(isset($data['annee_scolaire'])?$data['annee_scolaire']:null);
        $object->setDateComite($dateComite);

        return $object;
    }


}