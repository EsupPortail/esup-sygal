<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\MailConfirmation;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;

class MailConfirmationHydrator extends DoctrineObject
{
    use IndividuServiceAwareTrait;

    /**
     * @param MailConfirmation $object
     * @return array
     */
    public function extract($object): array
    {
        $data = [];
        $data['idIndividu'] = $object->getIndividu()->getId();
        $data['individu'] = $object->getIndividu();
        $data['email'] = $object->getEmail();
        $data['refusListeDiff'] = (int)$object->getRefusListeDiff();

        return $data;
    }

    /**
     * @param array $data
     * @param MailConfirmation $object
     * @return MailConfirmation
     */
    public function hydrate(array $data, $object): MailConfirmation
    {
        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->find($data['idIndividu']);

        $object->setIndividu($individu);
        $object->setEmail($data['email']);
        $object->setRefusListeDiff((bool)$data['refusListeDiff']);

        return $object;
    }
}