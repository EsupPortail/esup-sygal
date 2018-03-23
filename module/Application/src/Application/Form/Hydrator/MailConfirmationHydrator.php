<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Service\Individu\IndividuServiceAwareTrait;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class MailConfirmationHydrator extends DoctrineObject
{
    use IndividuServiceAwareTrait;
    /**
     * Extract values from an object
     *
     * @param  MailConfirmation $mailConfirmation
     * @return array
     */
    public function extract($mailConfirmation)
    {
        //$data = parent::extract($mailConfirmation);
        $data = [];
        //var_dump($data);
        //var_dump($mailConfirmation);
        $data['idIndividu'] = $mailConfirmation->getIndividu()->getId();
        $data['individu'] = $mailConfirmation->getIndividu();
        $data['email'] = $mailConfirmation->getEmail();
        $data['code'] = $mailConfirmation->getCode();
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  MailConfirmation $object
     * @return MailConfirmation
     */
    public function hydrate(array $data, $object)
    {
        /** @var Individu $individu */
        $individu = $this->individuService->getIndviduById($data['idIndividu']);
        $object->setIndividu($individu);
        $object->setEmail($data['email']);
        $object->setCode($data['code']);
        return $object;
    }
}