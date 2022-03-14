<?php

namespace Information\Form;

use Information\Entity\Db\Information;
use Information\Service\InformationLangue\InformationLangueerviceAwareTrait;
use Laminas\Hydrator\HydratorInterface;

class InformationHydrator implements HydratorInterface
{
    use InformationLangueerviceAwareTrait;

    /**
     * @param Information $object
     * @return array
     */
    public function extract($object): array
    {
        return [
            'titre' => $object->getTitre(),
            'contenu' => $object->getContenu(),
            'priorite' => $object->getPriorite(),
            'visible' => $object->isVisible(),
            'langue' => $object->getLangue()->getId(),
        ];
    }

    /**
     * @param array $data
     * @param Information $object
     * @return Information
     */
    public function hydrate(array $data, $object)
    {
        $langue = $this->getInformationLangueService()->getLangue($data['langue']);

        $object->setTitre($data['titre']);
        $object->setContenu($data['contenu']);
        $object->setPriorite($data['priorite']);
        $object->setVisible( ($data['visible'] == 1)?true:false);
        $object->setLangue( $langue);

        return $object;
    }

}