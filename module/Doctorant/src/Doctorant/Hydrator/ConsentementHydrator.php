<?php

namespace Doctorant\Hydrator;

use Application\Entity\Db\MailConfirmation;
use Laminas\Hydrator\AbstractHydrator;
use Webmozart\Assert\Assert;

class ConsentementHydrator extends AbstractHydrator
{
    /**
     * @param MailConfirmation $object
     * @return array
     */
    public function extract($object): array
    {
        Assert::isInstanceOf($object, MailConfirmation::class);

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
        $object->setRefusListeDiff((bool)$data['refusListeDiff']);

        return $object;
    }
}