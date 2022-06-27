<?php

namespace Application\Form\Hydrator;

use Individu\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Individu\Service\IndividuServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class MailConfirmationHydrator extends DoctrineObject
{
    use IndividuServiceAwareTrait;
    /**
     * Extract values from an object
     *
     * @param  MailConfirmation $mailConfirmation
     * @return array
     */
    public function extract($mailConfirmation): array
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
        $individu = $this->individuService->getRepository()->find($data['idIndividu']);
        $object->setIndividu($individu);
        $object->setEmail($data['email']);
        $object->setCode($data['code']);
        return $object;
    }
}